<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommunityRequest;
use App\Http\Requests\UpdateCommunityRequest;
use App\Http\Requests\StoreCommunityPostRequest;
use App\Http\Requests\JoinCommunityRequest;
use App\Http\Resources\CommunityResource;
use App\Http\Resources\CommunityCollection;
use App\Http\Traits\ApiResponse;
use App\Models\Community;
use App\Models\CommunityMember;
use App\Models\CommunityPost;
use App\Models\CommunityPostImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CommunityController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of communities.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Community::with(['creator', 'members.user']);

            // Filter by privacy
            if ($request->has('is_private')) {
                $query->where('is_private', $request->boolean('is_private'));
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by user's communities
            if ($request->boolean('my_communities')) {
                $userId = Auth::id();
                $query->whereHas('members', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->where('status', 'approved');
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = $request->get('per_page', 15);
            $communities = $query->paginate($perPage);

            return $this->successResponse('Communities retrieved successfully', new CommunityCollection($communities));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve communities', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created community.
     */
    public function store(StoreCommunityRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $community = Community::create([
                'name' => $request->name,
                'description' => $request->description,
                'profile_image_url' => $request->profile_image_url,
                'is_private' => $request->boolean('is_private', false),
                'created_by' => Auth::id(),
            ]);

            // Automatically add creator as admin member
            CommunityMember::create([
                'community_id' => $community->id,
                'user_id' => Auth::id(),
                'role' => 'admin',
                'status' => 'approved',
                'joined_at' => now(),
            ]);

            DB::commit();

            $community->load(['creator', 'members']);

            return $this->successResponse('Community created successfully', $community, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create community', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified community.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $community = Community::with(['creator', 'members.user'])
                ->findOrFail($id);

            // Check if user can view private community
            if ($community->is_private) {
                $userId = Auth::id();
                $isMember = $community->members()
                    ->where('user_id', $userId)
                    ->where('status', 'approved')
                    ->exists();

                if (!$isMember) {
                    return $this->errorResponse('Access denied to private community', null, 403);
                }
            }

            return $this->successResponse('Community retrieved successfully', new CommunityResource($community));
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve community', $e->getMessage(), 500);
        }
    }

    /**
     * Update the specified community.
     */
    public function update(UpdateCommunityRequest $request, string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);

            // Check if user is admin
            $isAdmin = $community->members()
                ->where('user_id', Auth::id())
                ->where('role', 'admin')
                ->where('status', 'approved')
                ->exists();

            if (!$isAdmin) {
                return $this->errorResponse('Only community admins can update community details', null, 403);
            }

            $community->update($request->validated());
            $community->load(['creator', 'members']);

            return $this->successResponse('Community updated successfully', $community);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update community', $e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified community.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);

            // Check if user is the creator
            if ($community->created_by !== Auth::id()) {
                return $this->errorResponse('Only community creator can delete the community', null, 403);
            }

            $community->delete();

            return $this->successResponse('Community deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete community', $e->getMessage(), 500);
        }
    }

    /**
     * Join a community.
     */
    public function join(JoinCommunityRequest $request, string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);
            $userId = Auth::id();

            // Check if already a member
            $existingMember = CommunityMember::where('community_id', $id)
                ->where('user_id', $userId)
                ->first();

            if ($existingMember) {
                if ($existingMember->status === 'approved') {
                    return $this->errorResponse('You are already a member of this community', null, 400);
                } elseif ($existingMember->status === 'pending') {
                    return $this->errorResponse('Your membership request is pending approval', null, 400);
                }
            }

            // Create or update membership
            $status = $community->is_private ? 'pending' : 'approved';
            $joinedAt = $community->is_private ? null : now();

            CommunityMember::updateOrCreate(
                [
                    'community_id' => $id,
                    'user_id' => $userId,
                ],
                [
                    'role' => 'member',
                    'status' => $status,
                    'joined_at' => $joinedAt,
                ]
            );

            $message = $community->is_private 
                ? 'Membership request sent successfully' 
                : 'Joined community successfully';

            return $this->successResponse($message);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to join community', $e->getMessage(), 500);
        }
    }

    /**
     * Leave a community.
     */
    public function leave(string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);
            $userId = Auth::id();

            // Check if user is the creator
            if ($community->created_by === $userId) {
                return $this->errorResponse('Community creator cannot leave the community', null, 400);
            }

            $member = CommunityMember::where('community_id', $id)
                ->where('user_id', $userId)
                ->first();

            if (!$member) {
                return $this->errorResponse('You are not a member of this community', null, 400);
            }

            $member->delete();

            return $this->successResponse('Left community successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to leave community', $e->getMessage(), 500);
        }
    }

    /**
     * Get community members.
     */
    public function members(Request $request, string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);

            // Check if user can view members
            if ($community->is_private) {
                $userId = Auth::id();
                $isMember = $community->members()
                    ->where('user_id', $userId)
                    ->where('status', 'approved')
                    ->exists();

                if (!$isMember) {
                    return $this->errorResponse('Access denied to private community members', null, 403);
                }
            }

            $query = CommunityMember::with('user')
                ->where('community_id', $id);

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            } else {
                $query->where('status', 'approved'); // Default to approved members
            }

            // Filter by role
            if ($request->has('role')) {
                $query->where('role', $request->role);
            }

            $members = $query->orderBy('joined_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse('Community members retrieved successfully', $members);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve community members', $e->getMessage(), 500);
        }
    }

    /**
     * Approve or reject membership request.
     */
    public function approveMember(Request $request, string $id, string $memberId): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);

            // Check if user is admin
            $isAdmin = $community->members()
                ->where('user_id', Auth::id())
                ->where('role', 'admin')
                ->where('status', 'approved')
                ->exists();

            if (!$isAdmin) {
                return $this->errorResponse('Only community admins can approve members', null, 403);
            }

            $member = CommunityMember::where('community_id', $id)
                ->where('user_id', $memberId)
                ->where('status', 'pending')
                ->firstOrFail();

            $action = $request->input('action', 'approve'); // approve or reject

            if ($action === 'approve') {
                $member->update([
                    'status' => 'approved',
                    'joined_at' => now(),
                ]);
                $message = 'Member approved successfully';
            } else {
                $member->delete();
                $message = 'Member request rejected successfully';
            }

            return $this->successResponse($message);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to process member request', $e->getMessage(), 500);
        }
    }

    /**
     * Create a post in the community.
     */
    public function createPost(StoreCommunityPostRequest $request, string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);

            // Check if user is an admin member
            $isAdmin = $community->members()
                ->where('user_id', Auth::id())
                ->where('role', 'admin')
                ->where('status', 'approved')
                ->exists();

            if (!$isAdmin) {
                return $this->errorResponse('Only community admins can create posts', null, 403);
            }

            DB::beginTransaction();

            $post = CommunityPost::create([
                'community_id' => $id,
                'admin_id' => Auth::id(),
                'caption' => $request->caption,
            ]);

            // Add images if provided
            if ($request->has('images') && is_array($request->images)) {
                foreach ($request->images as $index => $imageUrl) {
                    CommunityPostImage::create([
                        'post_id' => $post->id,
                        'image_url' => $imageUrl,
                        'position' => $index + 1,
                    ]);
                }
            }

            DB::commit();

            $post->load(['admin', 'images']);

            return $this->successResponse('Community post created successfully', $post, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->errorResponse('Failed to create community post', $e->getMessage(), 500);
        }
    }

    /**
     * Get community posts.
     */
    public function posts(Request $request, string $id): JsonResponse
    {
        try {
            $community = Community::findOrFail($id);

            // Check if user can view posts
            if ($community->is_private) {
                $userId = Auth::id();
                $isMember = $community->members()
                    ->where('user_id', $userId)
                    ->where('status', 'approved')
                    ->exists();

                if (!$isMember) {
                    return $this->errorResponse('Access denied to private community posts', null, 403);
                }
            }

            $posts = CommunityPost::with(['admin', 'images'])
                ->where('community_id', $id)
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 15));

            return $this->successResponse('Community posts retrieved successfully', $posts);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve community posts', $e->getMessage(), 500);
        }
    }
}
