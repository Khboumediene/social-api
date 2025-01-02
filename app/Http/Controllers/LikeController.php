<?php

namespace App\Http\Controllers;

use App\Models\Like;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(
 *     path="/api/likes"
 * )
 */
class LikeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/likes",
     *     summary="Create a like for a post",
     *     description="Allows a user to like a specific post.",
     *     operationId="likePost",
     *     tags={"Like"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"post_id", "profile_id"},
     *                 @OA\Property(property="post_id", type="integer", description="ID of the post to like"),
     *                 @OA\Property(property="profile_id", type="integer", description="ID of the profile liking the post")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like created successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="Like ID"),
     *             @OA\Property(property="profile_id", type="integer", description="Profile ID that liked the post"),
     *             @OA\Property(property="post_id", type="integer", description="Post ID that is liked")
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
        $validatedData = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $like = Like::firstOrCreate(
            [
                'profile_id' => $validatedData['profile_id'],
                'post_id' => $validatedData['post_id']
            ]
        );

        return response()->json($like, 201);
    }

    /**
     * @OA\Delete(
     *     path="/api/likes",
     *     summary="Remove a like from a post",
     *     description="Allows a user to remove their like from a specific post.",
     *     operationId="removeLike",
     *     tags={"Like"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"post_id", "profile_id"},
     *                 @OA\Property(property="post_id", type="integer", description="ID of the post to unlike"),
     *                 @OA\Property(property="profile_id", type="integer", description="ID of the profile removing the like")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like removed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Like not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy(Request $request)
    {
        $validatedData = $request->validate([
            'post_id' => 'required|exists:posts,id',
            'profile_id' => 'required|exists:profiles,id',
        ]);

        $like = Like::where('post_id', $validatedData['post_id'])
                    ->where('profile_id', $validatedData['profile_id'])
                    ->first();

        if (!$like) {
            return response()->json(['message' => 'Like not found'], 403);
        }

        $like->delete();

        return response()->json(['message' => 'Like removed successfully']);
    }
}
