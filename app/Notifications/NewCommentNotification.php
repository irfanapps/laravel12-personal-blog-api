<?php

namespace App\Notifications;

use App\Models\Comment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str; // For string helpers

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $postTitle = Str::limit($this->comment->post->title, 50);
        $commentExcerpt = Str::limit(strip_tags($this->comment->content), 100);
        $commenterName = $this->comment->user->name;

        return (new MailMessage)
            ->subject("New Comment on Your Post: {$postTitle}")
            ->greeting("Hello {$notifiable->name},")
            ->line(new HtmlString(
                "<strong>{$commenterName}</strong> has commented on your post: <strong>{$postTitle}</strong>"
            ))
            ->line(new HtmlString(
                "Comment: <em>\"{$commentExcerpt}\"</em>"
            ));
        // ->action(
        //     'View Comment',
        //     route(
        //         'posts.show',
        //         [
        //             'post' => $this->comment->post_id,
        //             '#comment-' . $this->comment->id
        //         ]
        //     )
        // );
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'new-comment',
            'comment_id' => $this->comment->id,
            'post_id' => $this->comment->post_id,
            'post_title' => Str::limit($this->comment->post->title, 50),
            'commenter_id' => $this->comment->user_id,
            'commenter_name' => $this->comment->user->name,
            'comment_excerpt' => Str::limit(strip_tags($this->comment->content), 100),
            // 'url' => route('posts.show', [
            //     'post' => $this->comment->post_id,
            //     '#comment-' . $this->comment->id
            // ])
        ];
    }
}
