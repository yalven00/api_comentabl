<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\News;
use App\Models\User;
use App\Models\VideoPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
       
        $users = $this->createUsers();
        
        $news = $this->createNews();
        
        $videoPosts = $this->createVideoPosts();
        
        $this->createComments($users, $news, $videoPosts);
        
    }
    
    private function createUsers()
    {  

        $admin = User::create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
            'password' => Hash::make('qwerty12345'),
            'email_verified_at' => now(),
        ]);
        
        $users = User::factory()->count(10)->create([
            'password' => Hash::make('password'),
        ]);
        
        return $users->prepend($admin);
    }
    
    private function createNews()
    {

        $news = News::factory()->count(15)->create();
        $popularNews = News::factory()->count(5)->create();
        return $news->merge($popularNews);
    }
    
    private function createVideoPosts()
    {
        
        $videoPosts = VideoPost::factory()->count(10)->create();
        $popularVideos = VideoPost::factory()->count(3)->create();
        return $videoPosts->merge($popularVideos);
    }
    
    private function createComments($users, $news, $videoPosts)
    {
        
        $commentableItems = collect([]);
        foreach ($news as $item) {
            $commentableItems->push([
                'type' => News::class,
                'id' => $item->id,
                'is_popular' => $item->id <= 5, 
            ]);
        }
        
        foreach ($videoPosts as $item) {
            $commentableItems->push([
                'type' => VideoPost::class,
                'id' => $item->id,
                'is_popular' => $item->id <= 3, 
            ]);
        }
        
        $commentCount = 0;
        $replyCount = 0;
        
        foreach ($commentableItems as $item) {

            $commentsPerItem = $item['is_popular'] ? rand(20, 50) : rand(5, 15);
            
            for ($i = 0; $i < $commentsPerItem; $i++) {
                $comment = Comment::create([
                    'content' => $this->generateCommentContent(),
                    'user_id' => $users->random()->id,
                    'commentable_type' => $item['type'],
                    'commentable_id' => $item['id'],
                    'parent_id' => null,
                ]);
                
                $commentCount++;
                
        
                if (rand(0, 100) > 70) { 
                    $repliesCount = rand(1, 5);
                    
                    for ($j = 0; $j < $repliesCount; $j++) {
                        Comment::create([
                            'content' => $this->generateReplyContent(),
                            'user_id' => $users->random()->id,
                            'commentable_type' => Comment::class,
                            'commentable_id' => $comment->id,
                            'parent_id' => $comment->id,
                        ]);
                        
                        $replyCount++;
                        
  
                        if (rand(0, 100) > 80) { 
                            Comment::create([
                                'content' => $this->generateNestedReplyContent(),
                                'user_id' => $users->random()->id,
                                'commentable_type' => Comment::class,
                                'commentable_id' => $comment->id,
                                'parent_id' => $comment->id,
                            ]);
                            
                            $replyCount++;
                        }
                    }
                }
            }
            
  
            $this->updateRepliesCounters($item['type'], $item['id']);
        }
        
    }
    
    private function generateCommentContent(): string
    {
        $comments = [

    'Excellent article! Very informative.',
    'Thanks for the useful information!',
    'Interesting perspective on the problem.',
    'Would like to see more details on this topic.',
    'Completely agree with the author!',
    'Have a few questions about the material...',
    'Great work! Looking forward to the continuation.',
    'Very relevant topic these days.',
    'It would be great to add more examples.',
    'Thank you for sharing your experience!',
    'Interesting, how does this work in practice?',
    'Good article, but there are some inaccuracies.',
    'Excellent material for reflection!',
    'Don\'t agree with all points, but overall good.',
    'Was waiting for this article! Thank you!',
    'Very detailed and clearly explained.',
    'Useful information for beginners.',
    'Interesting conclusions, thank you!',
    'Well-structured material.',
    'Will recommend to friends!',

    ];
        
        return $comments[array_rand($comments)];
    }
    
    private function generateReplyContent(): string
    {
        $replies = [

    'I agree with you!',
    'Great addition!',
    'Thanks for the clarification!',
    'Good point!',
    'Interesting comment!',
    'Fully support!',
    'Adding from my side...',
    'I have similar experience!',
    'Thank you for the explanation!',
    'Well noticed!',
    'Interesting point of view!',
    'Agree, but there are nuances...',
    'Thank you for the comment!',
    'Excellent observation!',
    'Support your opinion!',

    ];
        
        return $replies[array_rand($replies)];
    }
    
    private function generateNestedReplyContent(): string
    {
        $nestedReplies = [

    'Exactly!',
    'Rightly noted!',
    '100% agree!',
    'Exactly!',
    'Correct!',
    'Absolutely right!',
    'Sign under every word!',
    'Can\'t disagree!',
    'That\'s what we need!',
    'Spot on!',

        ];
        
        return $nestedReplies[array_rand($nestedReplies)];
    }
    
    private function updateRepliesCounters(string $type, int $id): void
    {
        $comments = Comment::where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->whereNull('parent_id')
            ->get();
            
        foreach ($comments as $comment) {
            $repliesCount = Comment::where('parent_id', $comment->id)->count();
            $comment->update(['replies_count' => $repliesCount]);
        }
    }
}
