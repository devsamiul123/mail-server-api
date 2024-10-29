<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Mail;
use Webklex\PHPIMAP\ClientManager;

class MailController extends Controller
{
    public function sendMail(Request $request)
    {
        $request->validate([
            'to' => 'required|email',
            'subject' => 'required|string',
            'body' => 'required|string',
        ]);

        $emailData = [
            'to' => $request->to,
            'subject' => $request->subject,
            'body' => $request->body,
        ];

        Mail::raw($emailData['body'], function ($message) use ($emailData) {
            $message->to($emailData['to'])
                ->subject($emailData['subject']);
        });

        return response()->json(['message' => 'Mail sent successfully']);
    }

    public function getInbox()
    {
        $client = (new ClientManager())->make([
            'host' => env('IMAP_HOST'),
            'port' => env('IMAP_PORT'),
            'encryption' => env('IMAP_ENCRYPTION'),
            'validate_cert' => true,
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'protocol' => 'imap'
        ]);

        $client->connect();
        $inbox = $client->getFolder('INBOX');
        $messages = $inbox->messages()->all()->get();

        $emails = [];
        foreach ($messages as $message) {
            $EmailHead = parseEmailHeaders($message->header->raw);
            $NameAndEmail = extractNameAndEmail($EmailHead['From']);
            $emails[] = [
                'head' => [
                    'from-name' => $NameAndEmail['name'],
                    'from-email' => $NameAndEmail['email'],
                    'to' => $EmailHead['To'],
                    'date' => $EmailHead['Date'],
                    'subject' => $EmailHead['Subject'],
                    'delivered-to' => $EmailHead['Delivered-To'],
                ],
                'body-txt' => $message->bodies['text'],
                'body-html' => $message->bodies['html'],
            ];
        }

        return response()->json($emails);
        // return response()->json($messages);
    }

    public function getUnreadEmails()
    {
        $client = (new ClientManager())->make([
            'host' => env('IMAP_HOST'),
            'port' => env('IMAP_PORT'),
            'encryption' => env('IMAP_ENCRYPTION'),
            'validate_cert' => true,
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'protocol' => 'imap'
        ]);

        $client->connect();
        $inbox = $client->getFolder('INBOX');
        $messages = $inbox->messages()->unseen()->get();

        $emails = [];
        foreach ($messages as $message) {
            $EmailHead = parseEmailHeaders($message->header->raw);
            $NameAndEmail = extractNameAndEmail($EmailHead['From']);
            $emails[] = [
                'head' => [
                    'from-name' => $NameAndEmail['name'],
                    'from-email' => $NameAndEmail['email'],
                    'to' => $EmailHead['To'],
                    'date' => $EmailHead['Date'],
                    'subject' => $EmailHead['Subject'],
                    'delivered-to' => $EmailHead['Delivered-To'],
                ],
                'body-txt' => $message->bodies['text'],
                'body-html' => $message->bodies['html'],
            ];
        }

        return response()->json($emails);
    }

    public function searchInbox(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
        ]);

        $client = (new ClientManager())->make([
            'host' => env('IMAP_HOST'),
            'port' => env('IMAP_PORT'),
            'encryption' => env('IMAP_ENCRYPTION'),
            'validate_cert' => true,
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'protocol' => 'imap'
        ]);

        $client->connect();
        $inbox = $client->getFolder('INBOX');
        $messages = $inbox->messages()->all()->get();

        $filteredEmails = [];
        foreach ($messages as $message) {
            $EmailHead = parseEmailHeaders($message->header->raw);
            $NameAndEmail = extractNameAndEmail($EmailHead['From']);
            if (str_contains(strtolower($NameAndEmail['email']), strtolower($request->email))) {
                $filteredEmails[] = [
                    'head' => [
                    'from-name' => $NameAndEmail['name'],
                    'from-email' => $NameAndEmail['email'],
                    'to' => $EmailHead['To'],
                    'date' => $EmailHead['Date'],
                    'subject' => $EmailHead['Subject'],
                    'delivered-to' => $EmailHead['Delivered-To'],
                    ],
                    'body-txt' => $message->bodies['text'],
                    'body-html' => $message->bodies['html'],
                ];
            }
        }

        if(empty($filteredEmails)){
            $filteredEmails[] = [
                'response' => 'No Mail Found On That Address'
            ];
        }

        return response()->json($filteredEmails);
    }

    public function getSpamMails()
    {
        $client = (new ClientManager())->make([
            'host' => env('IMAP_HOST'),
            'port' => env('IMAP_PORT'),
            'encryption' => env('IMAP_ENCRYPTION'),
            'validate_cert' => true,
            'username' => env('IMAP_USERNAME'),
            'password' => env('IMAP_PASSWORD'),
            'protocol' => 'imap'
        ]);

        $client->connect();
        $inbox = $client->getFolder('[Gmail]/Spam');
        $messages = $inbox->messages()->all()->get();

        $emails = [];
        foreach ($messages as $message) {
            $EmailHead = parseEmailHeaders($message->header->raw);
            $NameAndEmail = extractNameAndEmail($EmailHead['From']);
            $emails[] = [
                'head' => [
                    'from-name' => $NameAndEmail['name'],
                    'from-email' => $NameAndEmail['email'],
                    'to' => $EmailHead['To'],
                    'date' => $EmailHead['Date'],
                    'subject' => $EmailHead['Subject'],
                    'delivered-to' => $EmailHead['Delivered-To'],
                ],
                'body-txt' => $message->bodies['text'],
                'body-html' => $message->bodies['html'],
            ];
        }

        if(empty($emails)){
            $emails[] = [
                'response' => 'Hurray!!! No Spam Mail.'
            ];
        }

        return response()->json($emails);
    }


}

