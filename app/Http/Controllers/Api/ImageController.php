<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AppBaseController;
use App\Http\Requests\ImageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Image;
use OpenApi\Attributes as OA;

class ImageController extends AppBaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $images = Image::all();
        return $this->sendResponse($images, 'Lấy danh sách ảnh thành công');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    #[OA\Post(
        path: '/api/upload-images',
        summary: 'Upload ảnh',
        description: 'Xử lý mảng ảnh gửi lên từ app. Format ảnh: {patient_code}_{date}{stt}',
        tags: ['Images'],
        security: [["BearerAuth" => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: [
                new OA\MediaType(
                    mediaType: 'multipart/form-data',
                    schema: new OA\Schema(
                        type: 'object',
                        required: ['images'],
                        properties: [
                            new OA\Property(
                                property: 'images',
                                type: 'array',
                                items: new OA\Items(
                                    type: 'string',
                                    format: 'binary'
                                )
                            ),
                        ]
                    ),
                ),
            ]
        ),
        responses: [
            new OA\Response(
                response: '200',
                description: 'Ảnh đã được lưu thành công',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'message', type: 'string', example: 'Ảnh đã được lưu thành công'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                type: 'object',
                                properties: [
                                    new OA\Property(property: 'patient_code', type: 'string', example: '12345678'),
                                    new OA\Property(property: 'date', type: 'string', example: '07042026'),
                                    new OA\Property(property: 'stt', type: 'string', example: '001'),
                                    new OA\Property(property: 'file_name', type: 'string', example: '12345678_07042026001.jpg'),
                                    new OA\Property(property: 'file_size', type: 'integer', example: 102400),
                                    new OA\Property(property: 'mime_type', type: 'string', example: 'image/jpeg'),
                                    new OA\Property(property: 'url', type: 'string', example: 'images/abc123.jpg'),
                                    new OA\Property(property: 'created_by', type: 'integer', example: 1),
                                ]
                            )
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: '401',
                description: 'Chưa đăng nhập',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Vui lòng đăng nhập để thực hiện thao tác này.'),
                    ]
                )
            ),
            new OA\Response(
                response: '422',
                description: 'Lỗi validate',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Vui lòng chọn ảnh'),
                    ]
                )
            ),
            new OA\Response(
                response: '500',
                description: 'Lỗi server',
                content: new OA\JsonContent(
                    type: 'object',
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: false),
                        new OA\Property(property: 'message', type: 'string', example: 'Server error'),
                    ]
                )
            ),
        ]
    )]
    public function upload(ImageRequest $request)
    {
        $request->validated();

        $user = $request->user();

        $results = [];

        $files = $request->file('images', []);

        if (! is_array($files)) {
            $files = [$files];
        }

        if (count($files) === 0) {
            return $this->sendValidationError('Vui lòng chọn ảnh');
        }

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();

            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);

            /**
             * Format: 12345678_07042026001
             */
            if (! preg_match('/^(\d+)_([0-9]{8})([0-9]{3})$/', $nameWithoutExt, $matches)) {
                return $this->sendError("File name không đúng format: $originalName");
            }

            $patientCode = $matches[1]; // 12345678
            $date = $matches[2];        // 07042026
            $stt = $matches[3];         // 001 (optional)

            $path = Storage::disk('public')->putFile('images', $file);

            Image::create([
                'patient_code' => $patientCode,
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'url' => $path,
                'created_by' => $user->id,
            ]);

            $results[] = [
                'patient_code' => $patientCode,
                'date' => $date,
                'stt' => $stt,
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'url' => $path,
                'created_by' => $user->id,
            ];
        }

        return $this->sendResponse($results, 'Ảnh đã được lưu thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $patientCode)
    {
        $images = Image::where('patient_code', $patientCode)->get();
        return $this->sendResponse($images, "Lấy danh sách ảnh của bệnh nhân {$patientCode} thành công");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
