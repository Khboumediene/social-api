<?php

namespace App\Http\Controllers;

use App\Models\Follower;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(
 *     path="/api/followers"
 * )
 */
class FollowerController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/followers",
     *     summary="Follow a user",
     *     description="Allows a user to follow another user.",
     *     operationId="followUser",
     *     tags={"Follower"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"follower_id", "followed_id"},
     *                 @OA\Property(property="follower_id", type="integer", description="ID of the profile following the other user"),
     *                 @OA\Property(property="followed_id", type="integer", description="ID of the profile being followed")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Follow created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="Follow relation ID"),
     *             @OA\Property(property="follower_id", type="integer", description="Follower profile ID"),
     *             @OA\Property(property="followed_id", type="integer", description="Followed profile ID")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'follower_id' => 'required|exists:profiles,id',
            'followed_id' => 'required|exists:profiles,id',
        ]);

        $follower = Follower::firstOrCreate(
            [
                'follower_id' => $request->follower_id,
                'followed_id' => $request->followed_id,
            ]
        );
        
        return response()->json($follower, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/followers",
     *     summary="Unfollow a user",
     *     description="Allows a user to unfollow another user.",
     *     operationId="unfollowUser",
     *     tags={"Follower"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"follower_id", "followed_id"},
     *                 @OA\Property(property="follower_id", type="integer", description="ID of the profile unfollowing the other user"),
     *                 @OA\Property(property="followed_id", type="integer", description="ID of the profile being unfollowed")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Unfollowed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Follow relation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'follower_id' => 'required|exists:profiles,id',
            'followed_id' => 'required|exists:profiles,id',
        ]);

        $follower = Follower::where('follower_id', $request->follower_id)
            ->where('followed_id', $request->followed_id)
            ->first();

        if (!$follower) {
            return response()->json(['message' => 'Follow relation not found'], 403);
        }

        $follower->delete();

        return response()->json(['message' => 'Unfollowed successfully']);
    }
}
