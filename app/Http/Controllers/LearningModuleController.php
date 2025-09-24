<?php

namespace App\Http\Controllers;

use App\Models\LearningModule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="LearningModule",
 *     description="Learning Module management endpoints"
 * )
 */
class LearningModuleController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/learning-modules",
     *     summary="Get list of learning modules",
     *     tags={"LearningModule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation"
     *     )
     * )
     */
    public function index()
    {
        return LearningModule::with('lecturers')->paginate(10);
    }

    /**
     * @OA\Post(
     *     path="/api/learning-modules",
     *     summary="Create a new learning module",
     *     tags={"LearningModule"},
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"name","code"},
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Learning module created successfully"
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255|unique:learning_modules',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('learning_modules', 'public');
        }

        $learningModule = LearningModule::create($data);

        return response()->json($learningModule, 201);
    }

    /**
     * @OA\Get(
     *     path="/api/learning-modules/{id}",
     *     summary="Get a specific learning module",
     *     tags={"LearningModule"},
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
        $learningModule = LearningModule::with('lecturers')->findOrFail($id);
        return response()->json($learningModule);
    }

    /**
     * @OA\Put(
     *     path="/api/learning-modules/{id}",
     *     summary="Update a specific learning module",
     *     tags={"LearningModule"},
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
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="photo", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Learning module updated successfully"
     *     )
     * )
     */
    public function update(Request $request, $id)
    {
        $learningModule = LearningModule::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255|unique:learning_modules,code,' . $id,
            'description' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $data = $request->all();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($learningModule->photo) {
                Storage::disk('public')->delete($learningModule->photo);
            }
            $data['photo'] = $request->file('photo')->store('learning_modules', 'public');
        }

        $learningModule->update($data);

        return response()->json($learningModule);
    }

    /**
     * @OA\Delete(
     *     path="/api/learning-modules/{id}",
     *     summary="Delete a specific learning module",
     *     tags={"LearningModule"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Learning module deleted successfully"
     *     )
     * )
     */
    public function destroy($id)
    {
        $learningModule = LearningModule::findOrFail($id);
        $learningModule->delete();
        return response()->json(null, 204);
    }
}