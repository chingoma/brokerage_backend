<?php

namespace App\Http\Controllers\Chatting;

use App\Helpers\Helper;
use App\Helpers\MessagingHelper;
use App\Http\Controllers\Controller;
use App\Models\Messaging\Message;
use App\Models\Messaging\MessageChat;
use App\Models\ProfilePlain;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChattingController extends Controller
{
    public function contacts(Request $request): JsonResponse
    {
        return response()->json(ProfilePlain::get());
    }

    public function chats(Request $request): JsonResponse
    {
        return response()->json(MessageChat::find($request->id));
    }

    public function friends(Request $request): JsonResponse
    {
        $friends = Message::whereIn('sender_id', [Helper::profile()->id])
            ->orWhereIn('receiver_id', [Helper::profile()->id])
            ->groupBy('receiver_id')
            ->pluck('receiver_id');
        $friends = array_diff($friends->toArray(), [Helper::profile()->id]);

        return response()->json(ProfilePlain::whereIn('id', $friends)->get());
    }

    public function profile(Request $request): JsonResponse
    {
        return response()->json(Helper::profile($request->id));
    }

    public function create(Request $request): JsonResponse
    {
        $message = new Message();
        $message->sender_id = Helper::profile()->id;
        $message->receiver_id = $request->receiver_id;
        $message->type = $request->type;

        if (strtolower($request->type) == 'text') {
            $message->message = $request->message;
        }

        if ($request->hasfile('file') && strtolower($request->tyep == 'media')) {
            $file = $per_page('file');
            $path = $file->store('public/chatmedia');
            $message->media_name = $file->hashName();
            $message->media_extension = $file->extension();
            $message->media_url = $path;
            $message->media_type = $file->getMimeType();
        }

        $message->status = 'sent';
        $message->save();
        $event = new MessagingHelper($message);
        event($event);

        $friends = Message::whereIn('sender_id', [Helper::profile()->id])
            ->orWhereIn('receiver_id', [Helper::profile()->id])
            ->groupBy('receiver_id')
            ->pluck('receiver_id');

        $friends = array_diff($friends->toArray(), [Helper::profile()->id]);
        $friends = ProfilePlain::whereIn('id', $friends)->get();

        return response()->json(['chats' => MessageChat::find($request->receiver_id), 'friends' => $friends]);
    }
}
