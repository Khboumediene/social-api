<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\PathItem(
 *     path="/api/profiles"
 * )
 * * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your token here"
 * )
 */


class ProfileController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/profiles",
     *     summary="Get all profiles",
     *     description="Retrieve a list of all user profiles.",
     *     operationId="getAllProfiles",
     *     tags={"Profile"},
     *     @OA\Response(
     *         response=200,
     *         description="A list of profiles",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer", description="Profile ID"),
     *                 @OA\Property(property="username", type="string", description="Username"),
     *                 @OA\Property(property="email", type="string", description="Email address"),
     *                 @OA\Property(property="profile_picture", type="string", description="Profile picture URL")
     *             )
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function index()
    {
        return response()->json(Profile::all());
    }

    /**
     * @OA\Get(
     *     path="/api/profiles/{id}",
     *     summary="Get a specific profile",
     *     description="Retrieve a specific user profile by ID.",
     *     operationId="getProfile",
     *     tags={"Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Profile ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile details",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer", description="Profile ID"),
     *             @OA\Property(property="username", type="string", description="Username"),
     *             @OA\Property(property="email", type="string", description="Email address"),
     *             @OA\Property(property="profile_picture", type="string", description="Profile picture URL")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function show($id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        return response()->json($profile);
    }

    /**
     * @OA\Post(
     *     path="/api/profiles",
     *     summary="Create a new profile",
     *     description="Create a new user profile with username, email, password, and profile picture.",
     *     operationId="createProfile",
     *     tags={"Profile"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"username", "email", "password"},
     *                 @OA\Property(property="username", type="string", description="Username"),
     *                 @OA\Property(property="email", type="string", format="email", description="Email address"),
     *                 @OA\Property(property="password", type="string", description="Password", minLength=8),
     *                 @OA\Property(property="profile_picture", type="string", format="binary", description="Profile picture (file upload)")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Profile created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message"),
     *             @OA\Property(property="profile", type="object",
     *                 @OA\Property(property="id", type="integer", description="Profile ID"),
     *                 @OA\Property(property="username", type="string", description="Username"),
     *                 @OA\Property(property="email", type="string", description="Email address")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", description="Validation error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'username' => 'required|string|unique:profiles|max:255',
            'email' => 'required|email|unique:profiles|max:255',
            'password' => 'required|string|min:8',
            'profile_picture' => 'nullable|image|mimes:jpg,jpeg,png,gif|max:2048',
        ]);

        $validatedData['password_hash'] = bcrypt($validatedData['password']);

        if ($request->hasFile('profile_picture')) {
            $validatedData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        $profile = Profile::create($validatedData);

        return response()->json([
            'message' => 'Profile created successfully',
            'profile' => $profile
        ], 201);
    }

    /**
 * @OA\Put(
 *     path="/api/profiles/{id}",
 *     summary="Update a profile",
 *     description="Update an existing profile with new data.",
 *     operationId="updateProfile",
 *     tags={"Profile"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Profile ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="application/json",
 *             @OA\Schema(
 *                 type="object",
 *                 required={"username", "email"},
 *                 @OA\Property(property="username", type="string", description="Username"),
 *                 @OA\Property(property="email", type="string", format="email", description="Email address"),
 *                 @OA\Property(property="password", type="string", description="New password", minLength=8),
 *                 @OA\Property(property="profile_picture", type="string", format="binary", description="Profile picture (file upload)")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Profile updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Success message"),
 *             @OA\Property(property="profile", type="object",
 *                 @OA\Property(property="id", type="integer", description="Profile ID"),
 *                 @OA\Property(property="username", type="string", description="Username"),
 *                 @OA\Property(property="email", type="string", description="Email address")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=400,
 *         description="Validation error",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", description="Validation error message")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Profile not found",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", description="Error message")
 *         )
 *     ),
 *     security={{"bearerAuth": {}}}
 * )
 */

    public function update(Request $request, $id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }
        $validatedData = $request->validate([
            'username' => 'sometimes|string|unique:profiles,username,' . $id . '|max:255',
            'email' => 'sometimes|email|unique:profiles,email,' . $id . '|max:255',
            'password' => 'sometimes|string|min:8',
            'profile_picture' => 'nullable|string',
        ]);

        if ($request->hasFile('profile_picture')) {
            $validatedData['profile_picture'] = $request->file('profile_picture')->store('profile_pictures', 'public');
        }

        if (!empty($validatedData['password'])) {
            $validatedData['password_hash'] = bcrypt($validatedData['password']);
            unset($validatedData['password']);
        }

        $profile->update($validatedData);

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/profiles/{id}",
     *     summary="Delete a profile",
     *     description="Delete a user profile by ID.",
     *     operationId="deleteProfile",
     *     tags={"Profile"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Profile ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Profile deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Profile not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
     *         )
     *     ),
     *     security={{"bearerAuth": {}}}
     * )
     */
    public function destroy($id)
    {
        $profile = Profile::find($id);
        if (!$profile) {
            return response()->json(['message' => 'Profile not found'], 404);
        }

        $profile->delete();
        return response()->json(['message' => 'Profile deleted successfully']);
    }
}
