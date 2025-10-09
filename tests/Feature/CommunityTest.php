<?php

use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\CommunityPostImage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('can create a community', function () {
    $user = User::factory()->create();
    
    $community = Community::create([
        'name' => 'Test Community',
        'description' => 'A test community',
        'is_private' => false,
        'created_by' => $user->id,
    ]);
    
    expect($community)->toBeInstanceOf(Community::class);
    expect($community->name)->toBe('Test Community');
    expect($community->creator->id)->toBe($user->id);
});

test('community has proper relationships', function () {
    $user = User::factory()->create();
    $admin = User::factory()->create();
    
    $community = Community::create([
        'name' => 'Test Community',
        'description' => 'A test community',
        'is_private' => false,
        'created_by' => $user->id,
    ]);
    
    // Add member
    CommunityMember::create([
        'community_id' => $community->id,
        'user_id' => $admin->id,
        'role' => 'admin',
        'status' => 'approved',
        'joined_at' => now(),
    ]);
    
    // Create post
    $post = CommunityPost::create([
        'community_id' => $community->id,
        'admin_id' => $admin->id,
        'caption' => 'Test post',
    ]);
    
    // Add image to post
    CommunityPostImage::create([
        'post_id' => $post->id,
        'image_url' => 'https://example.com/image.jpg',
        'position' => 1,
    ]);
    
    expect($community->members)->toHaveCount(1);
    expect($community->posts)->toHaveCount(1);
    expect($community->admins)->toHaveCount(1);
    expect($post->images)->toHaveCount(1);
});

test('can list communities via api', function () {
    $user = User::factory()->create();
    
    Community::factory()->count(3)->create([
        'created_by' => $user->id,
        'is_private' => false,
    ]);
    
    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/communities');
    
    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'profile_image_url',
                        'is_private',
                        'creator_name',
                        'members' => [
                            '*' => [
                                'id',
                                'user_id',
                                'user_name',
                                'role',
                                'status',
                                'joined_at',
                            ]
                        ],
                        'created_at',
                        'updated_at',
                    ]
                ]
            ]
        ]);
});

test('can create community via api', function () {
    $user = User::factory()->create();
    
    $communityData = [
        'name' => 'API Test Community',
        'description' => 'Created via API',
        'is_private' => false,
    ];
    
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/communities', $communityData);
    
    $response->assertStatus(201)
        ->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'id',
                'name',
                'description',
                'is_private',
                'created_by',
            ]
        ]);
    
    $this->assertDatabaseHas('communities', [
        'name' => 'API Test Community',
        'created_by' => $user->id,
    ]);
});

test('can join community via api', function () {
    $user = User::factory()->create();
    $member = User::factory()->create();
    
    $community = Community::create([
        'name' => 'Test Community',
        'description' => 'A test community',
        'is_private' => false,
        'created_by' => $user->id,
    ]);
    
    $response = $this->actingAs($member, 'sanctum')
        ->postJson("/api/communities/{$community->id}/join");
    
    $response->assertStatus(200);
    
    $this->assertDatabaseHas('community_members', [
        'community_id' => $community->id,
        'user_id' => $member->id,
        'status' => 'approved',
    ]);
});

test('private community requires approval to join', function () {
    $user = User::factory()->create();
    $member = User::factory()->create();
    
    $community = Community::create([
        'name' => 'Private Community',
        'description' => 'A private community',
        'is_private' => true,
        'created_by' => $user->id,
    ]);
    
    $response = $this->actingAs($member, 'sanctum')
        ->postJson("/api/communities/{$community->id}/join");
    
    $response->assertStatus(200);
    
    $this->assertDatabaseHas('community_members', [
        'community_id' => $community->id,
        'user_id' => $member->id,
        'status' => 'pending',
    ]);
});
