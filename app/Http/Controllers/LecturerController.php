<?php

namespace App\Http\Controllers;

use App\Models\Lecturer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Lecturer",
 *     description="Lecturer management endpoints"
 * )
 */
class LecturerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/lecturers",
     *     summary="Get list of lecturers",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        return Lecturer::with('learningModules')->paginate(10);
    }

    /**
     * @OA\Post(
     *     path="/api/lecturers",
     *     summary="Create a new lecturer",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","code"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Lecturer created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:lecturers',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('lecturers', 'public');
        }

        $lecturer = Lecturer::create($data);

        return response()->json($lecturer, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/lecturers/{id}",
     *     summary="Get a specific lecturer",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function show($id)
    {
        $lecturer = Lecturer::with('learningModules')->findOrFail($id);
        return response()->json($lecturer);
    }

    /**
     * @OA\Put(
     *     path="/api/lecturers/{id}",
     *     summary="Update a specific lecturer",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     * @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lecturer updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $lecturer = Lecturer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:lecturers,code,' . $id,
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($lecturer->photo) {
                Storage::disk('public')->delete($lecturer->photo);
            }
            $data['photo'] = $request->file('photo')->store('lecturers', 'public');
        }

        $lecturer->update($data);

        return response()->json($lecturer);
    }

    /**
     * @OA\Delete(
     *     path="/api/lecturers/{id}",
     *     summary="Delete a specific lecturer",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Lecturer deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $lecturer = Lecturer::findOrFail($id);
        $lecturer->delete();
        return response()->json(null, 204);
    }

    /**
     * @OA\Post(
     *     path="/api/lecturers/{id}/learning-modules",
     *     summary="Attach learning modules to lecturer",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"learning_module_ids"},
     *             @OA\Property(property="learning_module_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Learning modules attached successfully"
     *     )
     * )
     */
    public function attachLearningModules(Request $request, $id)
    {
        $lecturer = Lecturer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'learning_module_ids' => 'required|array',
            'learning_module_ids.*' => 'integer|exists:learning_modules,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lecturer->learningModules()->attach($request->learning_module_ids);

        return response()->json(['message' => 'Learning modules attached successfully']);
    }

    /**
     * @OA\Delete(
     *     path="/api/lecturers/{id}/learning-modules",
     *     summary="Detach learning modules from lecturer",
     *     tags={"Lecturer"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"learning_module_ids"},
     *             @OA\Property(property="learning_module_ids", type="array", @OA\Items(type="integer"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Learning modules detached successfully"
     *     )
     * )
     */
    public function detachLearningModules(Request $request, $id)
    {
        $lecturer = Lecturer::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'learning_module_ids' => 'required|array',
            'learning_module_ids.*' => 'integer|exists:learning_modules,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $lecturer->learningModules()->detach($request->learning_module_ids);

        return response()->json(['message' => 'Learning modules detached successfully']);
    }
}