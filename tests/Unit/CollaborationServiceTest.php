<?php

namespace Tests\Unit;

use App\Models\Page;
use App\Models\User;
use App\Services\PageBuilder\Core\CollaborationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollaborationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CollaborationService $service;
    protected Page $page;
    protected User $user;
    protected User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CollaborationService();
        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->page = Page::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_user_can_join_page(): void
    {
        $this->service->userEditingPage($this->page, $this->user);

        $users = $this->service->getActiveUsers($this->page);
        $this->assertCount(1, $users);
        $this->assertEquals($this->user->id, $users[0]['user_id']);
    }

    public function test_multiple_users_can_join(): void
    {
        $this->service->userEditingPage($this->page, $this->user);
        $this->service->userEditingPage($this->page, $this->otherUser);

        $users = $this->service->getActiveUsers($this->page);
        $this->assertCount(2, $users);
    }

    public function test_user_can_leave_page(): void
    {
        $this->service->userEditingPage($this->page, $this->user);
        $this->service->userLeftPage($this->page, $this->user);

        $users = $this->service->getActiveUsers($this->page);
        $this->assertCount(0, $users);
    }

    public function test_heartbeat_updates_presence(): void
    {
        $this->service->userEditingPage($this->page, $this->user);
        $this->service->heartbeat($this->page, $this->user, ['element_id' => 5]);

        $users = $this->service->getActiveUsers($this->page);
        $this->assertEquals(['element_id' => 5], $users[0]['cursor_position']);
    }

    public function test_can_lock_element(): void
    {
        $result = $this->service->lockElement($this->page, 1, $this->user);
        $this->assertTrue($result);
    }

    public function test_cannot_lock_locked_element_by_another_user(): void
    {
        $this->service->lockElement($this->page, 1, $this->user);

        $result = $this->service->lockElement($this->page, 1, $this->otherUser);
        $this->assertFalse($result);
    }

    public function test_same_user_can_relock_element(): void
    {
        $this->service->lockElement($this->page, 1, $this->user);
        $result = $this->service->lockElement($this->page, 1, $this->user);
        $this->assertTrue($result);
    }

    public function test_can_unlock_element(): void
    {
        $this->service->lockElement($this->page, 1, $this->user);
        $this->service->unlockElement($this->page, 1, $this->user);

        $lock = $this->service->getElementLock($this->page, 1);
        $this->assertNull($lock);
    }

    public function test_get_element_lock(): void
    {
        $this->service->lockElement($this->page, 1, $this->user);

        $lock = $this->service->getElementLock($this->page, 1);
        $this->assertNotNull($lock);
        $this->assertEquals($this->user->id, $lock['user_id']);
    }

    public function test_user_gets_color(): void
    {
        $this->service->userEditingPage($this->page, $this->user);

        $users = $this->service->getActiveUsers($this->page);
        $this->assertNotEmpty($users[0]['color']);
        $this->assertStringStartsWith('#', $users[0]['color']);
    }
}
