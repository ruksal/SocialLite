<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Log;
use Throwable; // Add this import for the catch block
use Google\Client as GoogleClient;
use Google\Service\Calendar as GoogleServiceCalendar;
use Google\Service\Gmail as GoogleServiceGmail;
use Google\Service\Tasks as GoogleServiceTasks;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        /** @var \Laravel\Socialite\Two\GoogleProvider $provider */
        $provider = Socialite::driver("google");

        return $provider->scopes([
                GoogleServiceCalendar::CALENDAR_READONLY,
                GoogleServiceGmail::GMAIL_READONLY,
                GoogleServiceTasks::TASKS_READONLY,
                'profile',
                'email',
            ])
            ->with(['prompt' => 'consent']) // Force consent screen to get refresh token
            ->redirect();
    }

    public function callbackGoogle()
    {
        try {
            $google_user = Socialite::driver("google")->user();

            $user = User::where('google_id', $google_user->id)->first();

            // Retrieve tokens
            $google_access_token = $google_user->token;
            $google_refresh_token = $google_user->refreshToken;

            if (!$user) {
                $new_user = User::create([
                    'name' => $google_user->getName(),
                    'email' => $google_user->getEmail(),
                    'google_id' => $google_user->getId(),
                    'google_access_token' => $google_access_token,
                   'google_refresh_token' => $google_refresh_token,
                    // The expires_in is also available as $google_user->expiresIn
                ]);
                Auth::login($new_user);
            } else {
                // Update existing user with new tokens
                $user->update([
                    'google_access_token' => $google_access_token,
                    'google_refresh_token' => $google_refresh_token,
                ]);
                Auth::login($user);
            }

            return redirect()->intended('dashboard');
        } catch (Throwable $th) {
            Log::error('Google OAuth Error: ' . $th->getMessage());
            dd('Something went wrong', $th->getMessage());
        }
    }

    /**
     * Get Google Client instance with user's access token.
     * Handles token refresh.
     *
     * @param \App\Models\User $user
     * @return \Google\Client
     */
    private function getGoogleClient(User $user)
    {
        $client = new GoogleClient();
        $client->setClientId(env('GOOGLE_CLIENT_ID'));
        $client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $client->setRedirectUri(env('GOOGLE_REDIRECT_URI'));
        $client->setAccessType('offline'); // Important for refresh token
        $client->setPrompt('consent'); // Important for refresh token

        $accessToken = [
            'access_token' => $user->google_access_token,
            'expires_in' => 3600, // Access tokens typically expire in 1 hour
            'refresh_token' => $user->google_refresh_token,
            'created' => time(), // Set a creation time for expiration check
        ];

        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                // Update the user's access token in the database
                $user->update(['google_access_token' => $client->getAccessToken()['access_token']]);
            } else {
                // No refresh token, force re-authentication
                Auth::logout();
                return redirect()->route('auth.google')->with('error', 'Your Google session has expired. Please re-authenticate.');
            }
        }

        return $client;
    }

    /**
     * Show Google Calendar events.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showCalendar(Request $request)
    {
        try {
            $user = Auth::user();
            $client = $this->getGoogleClient($user);
            $service = new GoogleServiceCalendar($client);

            // Get start and end of current month in RFC3339 format
            $startOfMonth = date('Y-m-01\T00:00:00P');
            $endOfMonth = date('Y-m-t\T23:59:59P');

            $calendarId = 'primary';
            $optParams = array(
                'maxResults' => 100,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => $startOfMonth,
                'timeMax' => $endOfMonth,
            );
            $events = $service->events->listEvents($calendarId, $optParams)->getItems();

            return view('google.calendar', compact('events'));

        } catch (\Exception $e) {
            Log::error('Google Calendar Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Could not fetch calendar events: ' . $e->getMessage());
        }
    }

    /**
     * Show Google Mail messages.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showEmail(Request $request)
    {
        try {
            $user = Auth::user();
            $client = $this->getGoogleClient($user);
            $service = new GoogleServiceGmail($client);

            // Fetch latest messages from the user's inbox
            $messages = [];
            $results = $service->users_messages->listUsersMessages('me', ['maxResults' => 10]);
            foreach ($results->getMessages() as $message) {
                $msg = $service->users_messages->get('me', $message->getId(), ['format' => 'full']);
                $headers = $msg->getPayload()->getHeaders();
                $subject = '';
                $from = '';
                $date = '';

                foreach ($headers as $header) {
                    if ($header->getName() == 'Subject') {
                        $subject = $header->getValue();
                    }
                    if ($header->getName() == 'From') {
                        $from = $header->getValue();
                    }
                    if ($header->getName() == 'Date') {
                        $date = $header->getValue();
                    }
                }

                $messages[] = [
                    'id' => $msg->getId(),
                    'subject' => $subject,
                    'from' => $from,
                    'date' => $date,
                ];
            }

            return view('google.email', compact('messages'));

        } catch (\Exception $e) {
            Log::error('Google Email Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Could not fetch email messages: ' . $e->getMessage());
        }
    }

    /**
     * Show Google ToDos (Tasks).
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function showToDos(Request $request)
    {
        try {
            $user = Auth::user();
            $client = $this->getGoogleClient($user);
            $service = new GoogleServiceTasks($client);

            $taskLists = $service->tasklists->listTasklists()->getItems();
            $tasksByList = [];

            // Get start and end of current month in RFC3339 format
            $startOfMonth = date('Y-m-01\T00:00:00P');
            $endOfMonth = date('Y-m-t\T23:59:59P');

            foreach ($taskLists as $taskList) {
                // Filter tasks by due date within current month
                $tasks = [];
                foreach ($service->tasks->listTasks($taskList->getId())->getItems() as $task) {
                    $due = $task->getDue();
                    if ($due) {
                        if ($due >= $startOfMonth && $due <= $endOfMonth) {
                            $tasks[] = $task;
                        }
                    } else {
                        // If no due date, include all tasks
                        $tasks[] = $task;
                    }
                }
                $tasksByList[] = [
                    'title' => $taskList->getTitle(),
                    'tasks' => $tasks,
                ];
            }

            return view('google.todos', compact('tasksByList'));

        } catch (\Exception $e) {
            Log::error('Google ToDos Error: ' . $e->getMessage());
            return redirect('/')->with('error', 'Could not fetch ToDo tasks: ' . $e->getMessage());
        }
    }

}
