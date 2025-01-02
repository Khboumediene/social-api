<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Profile;
use Illuminate\Http\Request;

/**
 * @OA\PathItem(
 *     path="/api/posts"
 * )
 */
class PostController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="Get all posts",
     *     description="Retrieve a list of all posts.",
     *     operationId="getAllPosts",
     *     tags={"Post"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of posts",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Post ID"),
     *                 @OA\Property(property="content", type="string", description="Post content"),
     *                 @OA\Property(property="image_url", type="string", description="Image URL"),
     *                 @OA\Property(property="profile", type="object", description="Profile of the post author",
     *                     @OA\Property(property="id", type="integer", description="Profile ID"),
     *                     @OA\Property(property="name", type="string", description="Profile name")
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
        return response()->json(Post::with('profile')->get());
    }

    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="Create a new post",
     *     description="Create a new post with content, an optional image, and link it to a profile.",
     *     operationId="createPost",
     *     tags={"Post"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"profile_id", "content"},
     *                 @OA\Property(property="profile_id", type="integer", description="Profile ID associated with the post"),
     *                 @OA\Property(property="content", type="string", description="Post content"),
     *                 @OA\Property(property="image_url", type="string", format="binary", description="Optional image file")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message"),
     *             @OA\Property(property="post", type="object",
     *                 @OA\Property(property="id", type="integer", description="Post ID"),
     *                 @OA\Property(property="content", type="string", description="Post content"),
     *                 @OA\Property(property="image_url", type="string", description="Image URL"),
     *                 @OA\Property(property="profile", type="object", description="Profile of the post author",
     *                     @OA\Property(property="id", type="integer", description="Profile ID"),
     *                     @OA\Property(property="name", type="string", description="Profile name")
     *                 ),
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'profile_id' => 'required|exists:profiles,id', 
            'content' => 'required|string',
            'image_url' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        if ($request->hasFile('image_url')) {
            $validatedData['image_url'] = $request->file('image_url')->store('profile_pictures', 'public');
        }

        // Retrieve the profile associated with the ID
        $profile = Profile::findOrFail($request->profile_id);

        // Create the post associated with the profile
        $post = $profile->posts()->create([
            'content' => $validatedData['content'],
            'image_url' => $validatedData['image_url'] ?? null,
        ]);

        return response()->json($post, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/posts/{id}",
     *     summary="Get a specific post",
     *     description="Retrieve a specific post by ID.",
     *     operationId="getPost",
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="Post ID"),
     *             @OA\Property(property="content", type="string", description="Post content"),
     *             @OA\Property(property="image_url", type="string", description="Image URL"),
     *             @OA\Property(property="profile", type="object", description="Profile of the post author",
     *                 @OA\Property(property="id", type="integer", description="Profile ID"),
     *                 @OA\Property(property="name", type="string", description="Profile name")
     *             ),
     *             @OA\Property(property="created_at", type="string", format="date-time", description="Creation time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function show($id)
    {
        $post = Post::with(['profile', 'comments'])->find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        return response()->json($post);
    }

    /**
 * @OA\Put(
 *     path="/api/posts/{id}",
 *     summary="Update an existing post",
 *     description="Update a post by ID, including content and optionally an image.",
 *     operationId="updatePost",
 *     tags={"Post"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Post ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"content"},
 *                 @OA\Property(property="content", type="string", description="Post content"),
 *                 @OA\Property(property="image_url", type="string", format="uri", description="Optional image URL")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Post updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Success message"),
 *             @OA\Property(property="post", type="object",
 *                 @OA\Property(property="id", type="integer", description="Post ID"),
 *                 @OA\Property(property="content", type="string", description="Post content"),
 *                 @OA\Property(property="image_url", type="string", description="Image URL"),
 *                 @OA\Property(property="profile", type="object", description="Profile of the post author",
 *                     @OA\Property(property="id", type="integer", description="Profile ID"),
 *                     @OA\Property(property="name", type="string", description="Profile name")
 *                 ),
 *             )
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */
    public function update(Request $request, $id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }

        $validatedData = $request->validate([ 
            'content' => 'required|string',
            'image_url' => 'nullable|string',
        ]);

        if ($request->hasFile('image_url')) {
            $validatedData['image_url'] = $request->file('image_url')->store('profile_pictures', 'public');
        }

        $post->update([
            'content' => $validatedData['content'] ?? $post->content,
            'image_url' => $validatedData['image_url'] ?? $post->image_url,
        ]);

        return response()->json($post);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{id}",
     *     summary="Delete a post",
     *     description="Delete a post by ID.",
     *     operationId="deletePost",
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Post ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Post not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy($id)
    {
        $post = Post::find($id);
        if (!$post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully']);
    }
}
