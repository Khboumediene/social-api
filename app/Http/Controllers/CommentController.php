<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(
 *     path="/api/comments"
 * )
 */
class CommentController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/comments",
     *     summary="Get all comments",
     *     description="Retrieve a list of all comments with associated profiles and posts.",
     *     operationId="getAllComments",
     *     tags={"Comment"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of comments",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Comment ID"),
     *                 @OA\Property(property="content", type="string", description="Comment content"),
     *                 @OA\Property(property="profile", type="object", description="Profile of the commenter",
     *                     @OA\Property(property="id", type="integer", description="Profile ID"),
     *                     @OA\Property(property="name", type="string", description="Profile name")
     *                 ),
     *                 @OA\Property(property="post", type="object", description="Post related to the comment",
     *                     @OA\Property(property="id", type="integer", description="Post ID"),
     *                     @OA\Property(property="content", type="string", description="Post content")
     *                 ),
     *                 @OA\Property(property="created_at", type="string", format="date-time", description="Creation time")
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index()
    {
        return response()->json(Comment::with('profile', 'post')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/comments",
     *     summary="Create a new comment",
     *     description="Create a new comment for a specific post and profile.",
     *     operationId="createComment",
     *     tags={"Comment"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"profile_id", "post_id", "content"},
     *                 @OA\Property(property="profile_id", type="integer", description="Profile ID of the commenter"),
     *                 @OA\Property(property="post_id", type="integer", description="Post ID the comment belongs to"),
     *                 @OA\Property(property="content", type="string", description="Content of the comment")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comment created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message"),
     *             @OA\Property(property="comment", type="object",
     *                 @OA\Property(property="id", type="integer", description="Comment ID"),
     *                 @OA\Property(property="content", type="string", description="Comment content"),
     *                 @OA\Property(property="profile", type="object", description="Profile of the commenter",
     *                     @OA\Property(property="id", type="integer", description="Profile ID"),
     *                     @OA\Property(property="name", type="string", description="Profile name")
     *                 ),
     *                 @OA\Property(property="post", type="object", description="Post related to the comment",
     *                     @OA\Property(property="id", type="integer", description="Post ID"),
     *                     @OA\Property(property="content", type="string", description="Post content")
     *                 ),
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'profile_id' => 'required|exists:profiles,id',
            'post_id' => 'required|exists:posts,id',
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'profile_id' => $request->profile_id,
            'post_id' => $request->post_id,
            'content' => $request->content,
        ]);

        return response()->json($comment, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/comments/{id}",
     *     summary="Get a specific comment",
     *     description="Retrieve a specific comment by ID, including the profile and post.",
     *     operationId="getComment",
     *     tags={"Comment"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="Comment ID"),
     *             @OA\Property(property="content", type="string", description="Comment content"),
     *             @OA\Property(property="profile", type="object", description="Profile of the commenter",
     *                 @OA\Property(property="id", type="integer", description="Profile ID"),
     *                 @OA\Property(property="name", type="string", description="Profile name")
     *             ),
     *             @OA\Property(property="post", type="object", description="Post related to the comment",
     *                 @OA\Property(property="id", type="integer", description="Post ID"),
     *                 @OA\Property(property="content", type="string", description="Post content")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", description="Creation time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function show($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }
        return response()->json($comment);
    }

    /**
     * @OA\Put(
     *     path="/api/comments/{id}",
     *     summary="Update an existing comment",
     *     description="Update a comment's content by ID.",
     *     operationId="updateComment",
     *     tags={"Comment"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"content"},
     *                 @OA\Property(property="content", type="string", description="Updated content of the comment")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message"),
     *             @OA\Property(property="comment", type="object",
     *                 @OA\Property(property="id", type="integer", description="Comment ID"),
     *                 @OA\Property(property="content", type="string", description="Updated comment content"),
     *                 @OA\Property(property="profile", type="object", description="Profile of the commenter",
     *                     @OA\Property(property="id", type="integer", description="Profile ID"),
     *                     @OA\Property(property="name", type="string", description="Profile name")
     *                 ),
     *                 @OA\Property(property="post", type="object", description="Post related to the comment",
     *                     @OA\Property(property="id", type="integer", description="Post ID"),
     *                     @OA\Property(property="content", type="string", description="Post content")
     *                 ),
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function update(Request $request, $id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $request->validate(['content' => 'required|string']);
        $comment->update($request->only('content'));
        return response()->json($comment);
    }

    /**
     * @OA\Delete(
     *     path="/api/comments/{id}",
     *     summary="Delete a comment",
     *     description="Delete a comment by ID.",
     *     operationId="deleteComment",
     *     tags={"Comment"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Comment ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Comment deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Comment not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy($id)
    {
        $comment = Comment::find($id);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $comment->delete();
        return response()->json(['message' => 'Comment deleted']);
    }
}
