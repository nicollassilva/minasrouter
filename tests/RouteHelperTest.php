<?php

use MinasRouter\Router\Route;
use PHPUnit\Framework\TestCase;
use MinasRouter\Helpers\Functions;
use MinasRouter\Router\RouteManager;

final class RouteHelperTest extends TestCase
{
    protected function setUp(): void
    {
        Route::start('http://localhost/');

        Route::get('/topic/{id}/comments', 'TopicController@comments')->name('topic.comments');
        Route::get('/topic/{id}/comment/{idComment}/show', 'TopicCommentController@show')->name('topic.comment.show');
        Route::get('/topic/{id}/comment/{idComment}/edit', 'TopicCommentController@edit')->name('topic.comment.edit');
        Route::put('/topic/comment/update', 'TopicCommentUpdate@index')->name('topic.comment.update');
    }
    /**
     * @test
     */
    public function check_if_router_helper_function_returns_a_collection()
    {
        $helperRouter = router();

        $this->assertInstanceOf(Functions::class, $helperRouter);
    }

    /**
     * @test
     */
    public function check_if_get_function_helper_returns_a_determinate_route()
    {
        $topicCommentsRoute = router()->get('topic.comments');

        $this->assertInstanceOf(RouteManager::class, $topicCommentsRoute);

        $this->assertSame('topic.comments', $topicCommentsRoute->getName());

        $this->assertSame('/topic/{id}/comments', $topicCommentsRoute->getOriginalRoute());

        $topicCommentShowRoute = router()->get('topic.comment.show');

        $this->assertInstanceOf(RouteManager::class, $topicCommentShowRoute);

        $this->assertSame('topic.comment.show', $topicCommentShowRoute->getName());

        $this->assertSame('/topic/{id}/comment/{idComment}/show', $topicCommentShowRoute->getOriginalRoute());
    }

    /**
     * @test
     */
    public function check_if_route_helper_function_replace_the_regex_with_parameters()
    {
        $scenaryOne = [
            route('topic.comment.edit'),
            route('topic.comment.edit', [12]),
            route('topic.comment.edit', [12, 534]),
            route('topic.comment.edit', ['topic', 534]),
            route('topic.comment.edit', [12, 'topic']),
        ];

        $scenaryOneExpected = [
            null, null, '/topic/12/comment/534/edit', '/topic/topic/comment/534/edit', '/topic/12/comment/topic/edit'
        ];

        foreach($scenaryOne as $key => $result) {
            $this->assertSame($scenaryOneExpected[$key], $result);
        }

        $scenaryTwo = [
            route('topic.comments'),
            route('topic.comments', 12),
            route('topic.comments', 'neymar')
        ];

        $scenaryTwoExpected = [
            null, '/topic/12/comments', '/topic/neymar/comments'
        ];
        
        foreach($scenaryTwo as $key => $result) {
            $this->assertSame($scenaryTwoExpected[$key], $result);
        }
        
        $scenaryThree = [
            route('topic.comment.update'),
            route('topic.comment.update', 12),
            route('topic.comment.update', [12]),
            route('topic.comment.update', ['oi', 12])
        ];

        $scenaryThreeExpected = '/topic/comment/update';

        foreach($scenaryThree as $result) {
            $this->assertSame($scenaryThreeExpected, $result);
        }
    }
}
