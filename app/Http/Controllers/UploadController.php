<?php

namespace App\Http\Controllers;

use App\Http\Requests\DestroyProfilePhotoRequest;
use App\Http\Requests\DestroyThumbnailRequest;
use App\Http\Requests\DestroyVideoRequest;
use App\Http\Requests\StoreUploadProfilePhotoRequest;
use App\Http\Requests\StoreUploadThumbnailRequest;
use App\Http\Requests\StoreUploadVideoRequest;
use App\Traits\HttpResponses;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Log\Logger;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Str;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Response;

#[Group("Upload File Management", "APIs for managing upload files")]
class UploadController extends Controller implements HasMiddleware
{
    use HttpResponses;
    protected Filesystem $disk;
    public function __construct(
        protected Logger $logger,
        FilesystemManager $storage,
    )
    {
        $this->disk = $storage->disk('s3');
    }

    public static function middleware() :array
    {
        return [ new Middleware('auth:api') ];
    }

    #[Endpoint('Upload photo users profile', <<<DESC
  This endpoint allows you to upload photo.
  It's a really useful endpoint, because this endpoint can upload photo and you must be authenticated.
 DESC)]
    #[BodyParam('image', 'file', true, 'File image for users profile', 'public/profile.jpg')]
    #[Authenticated(true)]
    #[Response(
        content: [
            'title' => 'Berhasil meyimpan foto profile',
            'code' => 200,
            'status'  => 'STATUS_OK',
            'data' => [
                'file_name' => 'profiles/profile-5eba3b1f7bc34.jpg',
                'file_url' => 'http://minio.example.com/bucket/profiles/profile-5eba3b1f7bc34.jpg'
            ]
        ],
        status: 200,
        description: 'Successfully'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengvalidasi permintaan',
                    'details' => [
                        'Foto profil wajib diunggah.'
                    ],
                    'code' => 400,
                    'status'  => 'STATUS_BAD_REQUEST',
                ]
            ]
        ],
        status: 400,
        description: 'Bad Request'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengunggah foto profile karena kegagalan server',
                    'details' => 'Sesuatu ada yang salah dengan server, tolong coba lagi',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]
        ],
        status: 500,
        description: 'Internal Server Error'
    )]
    public function storeProfilePhoto(StoreUploadProfilePhotoRequest $request) :JsonResponse
    {
        try {
            // Validate request
            $request->validated();

            // Get file from request
            $file = $request->file('image');
            $originalName = $file->getClientOriginalName();

            // Generate unique file name
            $fileName = 'profile-' . Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            // Log upload attempt
            $this->logger->info('Profile upload attempt', [
                'original_name' => $originalName,
                'target_name' => $fileName,
                'size' => $file->getSize(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Save file to s3 minio in profiles folder
            $path = $this->disk->putFileAs(
                'profiles',
                $file,
                $fileName
            );

            if (!$path) {
                throw new \Exception('gagal menyimpan foto ke penyimpanan objek');
            }

            // Generate URL
            $url = $this->disk->url($path);

            // Log successful upload
            $this->logger->info('Profile uploaded successfully', [
                'path' => $path,
                'url' => $url
            ]);
            return $this->successResponse([
                'title' => 'Berhasil menyimpan foto profile',
                'code' => 200,
                'status' => 'STATUS_OK',
                'data' => [
                    'file_name' => $path,
                    'file_url' => $url
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Profile upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($e,'Gagal mengunggah foto profile karena kegagalan server'));
        }
    }

    #[Endpoint('Upload photo thumbnail course', <<<DESC
  This endpoint allows you to upload thumbnail course.
  It's a really useful endpoint, because this endpoint can upload thumbnail course and you must be authenticated.
 DESC)]
    #[Authenticated(true)]
    #[BodyParam('thumbnail', 'file', true, 'File thumbnail', 'public/thumbnail.jpg')]
    #[Response(
        content: [
            'title' => 'Berhasil menyimpan thumbnail',
            'code' => 200,
            'status' => 'STATUS_OK',
            'data' => [
                'file_name' => 'thumbnails/thumbnail-5eba3b1f7bc34.jpg',
                'file_url' => 'http://minio.example.com/bucket/thumbnails/thumbnail-5eba3b1f7bc34.jpg'
            ]
        ],
        status: 200,
        description: 'Berhasil'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengvalidasi permintaan',
                    'details' => [
                        'Thumbnail wajib diunggah.'
                    ],
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST',
                ]
            ]
        ],
        status: 400,
        description: 'Bad Request'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengunggah thumbnail karena kegagalan server',
                    'details' => 'Sesuatu ada yang salah dengan server, tolong coba lagi',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]
        ],
        status: 500,
        description: 'Internal Server Error'
    )]
    public function storeCourseThumbnail(StoreUploadThumbnailRequest $request) :JsonResponse
    {
        try {
            // Validate request
            $request->validated();

            // Get file from request
            $file = $request->file('thumbnail');
            $originalName = $file->getClientOriginalName();

            // Generate unique file name
            $fileName = 'thumbnail-' . Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            // Log upload attempt
            $this->logger->info('Thumbnail upload attempt', [
                'original_name' => $originalName,
                'target_name' => $fileName,
                'size' => $file->getSize(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Save file to s3 minio in thumbnails folder
            $path = $this->disk->putFileAs(
                'thumbnails',
                $file,
                $fileName
            );

            if (!$path) {
                throw new \Exception('Gagal menyimpan thumbnail ke penyimpanan objek');
            }

            // Generate URL
            $url = $this->disk->url($path);

            // Log successful upload
            $this->logger->info('Thumbnail uploaded successfully', [
                'path' => $path,
                'url' => $url
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menyimpan thumbnail',
                'code' => 200,
                'status' => 'STATUS_OK',
                'data' => [
                    'file_name' => $path,
                    'file_url' => $url
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Thumbnail upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal mengunggah thumbnail karena kegagalan server'));
        }
    }

    #[Endpoint('Upload video course', <<<DESC
  This endpoint allows you to upload video course.
  It's a really useful endpoint, because this endpoint can upload video course and you must be authenticated.
 DESC)]
    #[BodyParam('video', 'file', true, 'File video', 'public/video.mp4')]
    #[Authenticated(true)]
    #[Response(
        content: [
            'title' => 'Berhasil menyimpan video',
            'code' => 200,
            'status' => 'STATUS_OK',
            'data' => [
                'file_name' => 'videos/video-5eba3b1f7bc34.mp4',
                'file_url' => 'http://minio.example.com/bucket/videos/video-5eba3b1f7bc34.mp4'
            ]
        ],
        status: 200,
        description: 'Berhasil'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengvalidasi permintaan',
                    'details' => [
                        'Video wajib diunggah.'
                    ],
                    'code' => 400,
                    'status' => 'STATUS_BAD_REQUEST',
                ]
            ]
        ],
        status: 400,
        description: 'Bad Request'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal mengunggah video karena kegagalan server',
                    'details' => 'Sesuatu ada yang salah dengan server, tolong coba lagi',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]
        ],
        status: 500,
        description: 'Internal Server Error'
    )]
    public function storeCourseVideo(StoreUploadVideoRequest $request) :JsonResponse
    {
        try {
            // Validate request
            $request->validated();

            // Get file from request
            $file = $request->file('video');
            $originalName = $file->getClientOriginalName();

            // Generate unique file name
            $fileName = 'video-' . Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();

            // Log upload attempt
            $this->logger->info('Video upload attempt', [
                'original_name' => $originalName,
                'target_name' => $fileName,
                'size' => $file->getSize(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Save file to s3 minio in videos folder
            $path = $this->disk->putFileAs(
                'videos',
                $file,
                $fileName
            );

            if (!$path) {
                throw new \Exception('Gagal menyimpan video ke penyimpanan objek');
            }

            // Generate URL
            $url = $this->disk->url($path);

            // Log successful upload
            $this->logger->info('Video uploaded successfully', [
                'path' => $path,
                'url' => $url
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menyimpan video',
                'code' => 200,
                'status' => 'STATUS_OK',
                'data' => [
                    'file_name' => $path,
                    'file_url' => $url
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Video upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal mengunggah video karena kegagalan server'));
        }
    }

    #[Endpoint('Delete photo users profile by image path', <<<DESC
  This endpoint allows you to delete photo users profile by image path.
  It's a really useful endpoint, because this endpoint can delete photo users profile by image path and you must be authenticated.
 DESC)]
    #[Authenticated(true)]
    #[BodyParam('image_path', 'string', true, 'Path image to delete', 'profiles/profile-5eba3b1f7bc34.jpg')]
    #[Response(
        content: [
            'title' => 'Berhasil menghapus foto profil',
            'code' => 200,
            'status' => 'STATUS_OK',
            'data' => null,
            'meta' => [
                'user_id' => 'ASIAJSIQSMAK...',
                'image_path' => 'profiles/profile-5eba3b1f7bc34.jpg'
            ]
        ],
        status: 200,
        description: 'Berhasil'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'File tidak ditemukan',
                    'details' => 'File yang diminta tidak ditemukan di penyimpanan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]
        ],
        status: 404,
        description: 'Not Found'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menghapus foto profil karena kegagalan server',
                    'details' => 'Sesuatu ada yang salah dengan server, tolong coba lagi',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]
        ],
        status: 500,
        description: 'Internal Server Error'
    )]
    public function destroyProfilePhoto(DestroyProfilePhotoRequest $request): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validated();
            $filePath = $validated['image_path'];

            if (!Str::startsWith($filePath, 'profiles/')) {
                return $this->errorResponse([
                    [
                        'title' => 'Gagal mengvalidasi permintaan',
                        'details' => ['Jalur Foto harus dimulai dengan profiles/'],
                        'code' => 400,
                        'status' => 'STATUS_BAD_REQUEST',
                    ]
                ]);
            }

            // Log delete attempt
            $this->logger->info('Profile delete attempt', [
                'file_path' => $filePath,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Check if file exists
            if (!$this->disk->exists($filePath)) {
                $this->logger->warning('Profile delete failed: file not found', [
                    'file_path' => $filePath
                ]);

                return $this->errorResponse([
                    [
                        'title' => 'File tidak ditemukan',
                        'details' => 'File yang diminta tidak ditemukan di penyimpanan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]);
            }

            // Delete file from s3 minio
            $deleted = $this->disk->delete($filePath);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus file dari penyimpanan');
            }

            // Log successful deletion
            $this->logger->info('Profile deleted successfully', [
                'file_path' => $filePath
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menghapus foto profil',
                'code' => 200,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => [
                    'user_id' => $request->user('api')->id,
                    'image_path' => $filePath,
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Profile delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus foto profil karena kegagalan server'));
        }
    }

    #[Endpoint('Delete thumbnail course by thumbnail path', <<<DESC
  This endpoint allows you to delete thumbnail course by thumbnail path.
  It's a really useful endpoint, because this endpoint can delete photo thumbnail by thumbnail path and you must be authenticated.
 DESC)]
    #[Authenticated(true)]
    #[BodyParam('thumbnail_path', 'string', true, 'Path thumbnail to delete', 'thumbnails/thumbnail-5eba3b1f7bc34.jpg')]
    #[Response(
        content: [
            'title' => 'Berhasil menghapus thumbnail',
            'code' => 200,
            'status' => 'STATUS_OK',
            'data' => null,
            'meta' => [
                'user_id' => 'ASIAJSIQSMAK...',
                'thumbnail_path' => 'thumbnails/thumbnail-5eba3b1f7bc34.jpg',
            ]
        ],
        status: 200,
        description: 'Berhasil'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'File tidak ditemukan',
                    'details' => 'File yang diminta tidak ditemukan di penyimpanan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]
        ],
        status: 404,
        description: 'Not Found'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menghapus thumbnail karena kegagalan server',
                    'details' => 'Sesuatu ada yang salah dengan server, tolong coba lagi',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]
        ],
        status: 500,
        description: 'Internal Server Error'
    )]
    public function destroyCourseThumbnail(DestroyThumbnailRequest $request): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validated();
            $filePath = $validated['thumbnail_path'];

            // Pastikan file yang dihapus berada di folder thumbnails
            if (!Str::startsWith($filePath, 'thumbnails/')) {
                return $this->errorResponse([
                    [
                        'title' => 'Gagal mengvalidasi permintaan',
                        'details' => ['Jalur File thumbnail harus dimulai dengan thumbnails/'],
                        'code' => 400,
                        'status' => 'STATUS_BAD_REQUEST',
                    ]
                ]);
            }

            // Log delete attempt
            $this->logger->info('Thumbnail delete attempt', [
                'file_path' => $filePath,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Check if file exists
            if (!$this->disk->exists($filePath)) {
                $this->logger->warning('Thumbnail delete failed: file not found', [
                    'file_path' => $filePath
                ]);

                return $this->errorResponse([
                    [
                        'title' => 'File tidak ditemukan',
                        'details' => 'File yang diminta tidak ditemukan di penyimpanan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]);
            }

            // Delete file from s3 minio
            $deleted = $this->disk->delete($filePath);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus file dari penyimpanan');
            }

            // Log successful deletion
            $this->logger->info('Thumbnail deleted successfully', [
                'file_path' => $filePath
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menghapus thumbnail',
                'code' => 200,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => [
                    'user_id' => $request->user('api')->id,
                    'thumbnail_path' => $filePath,
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Thumbnail delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus thumbnail karena kegagalan server'));
        }
    }

    #[Endpoint('Delete video course by video path', <<<DESC
  This endpoint allows you to delete video course by video path.
  It's a really useful endpoint, because this endpoint can delete video course by video path and you must be authenticated.
 DESC)]
    #[Authenticated(true)]
    #[BodyParam('video_path', 'string', true, 'video path to delete', 'videos/video-5eba3b1f7bc34.mp4')]
    #[Response(
        content: [
            'title' => 'Berhasil menghapus video',
            'code' => 200,
            'status' => 'STATUS_OK',
            'data' => null,
            'meta' => [
                'user_id' => 'ASIAJSIQSMAK...',
                'video_path' => 'videos/video-5eba3b1f7bc34.jpg',
            ]
        ],
        status: 200,
        description: 'Berhasil'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'File tidak ditemukan',
                    'details' => 'File yang diminta tidak ditemukan di penyimpanan',
                    'code' => 404,
                    'status' => 'STATUS_NOT_FOUND',
                ]
            ]
        ],
        status: 404,
        description: 'Not Found'
    )]
    #[Response(
        content: [
            'errors' => [
                [
                    'title' => 'Gagal menghapus video karena kegagalan server',
                    'details' => 'Sesuatu ada yang salah dengan server, tolong coba lagi',
                    'code' => 500,
                    'status' => 'INTERNAL_SERVER_ERROR',
                ]
            ]
        ],
        status: 500,
        description: 'Internal Server Error'
    )]
    public function destroyCourseVideo(DestroyVideoRequest $request): JsonResponse
    {
        try {
            // Validate request
            $validated = $request->validated();
            $filePath = $validated['video_path'];

            // Pastikan file yang dihapus berada di folder videos
            if (!Str::startsWith($filePath, 'videos/')) {
                return $this->errorResponse([
                    [
                        'title' => 'Gagal mengvalidasi permintaan',
                        'details' => ['Jalur Video File harus dimulai dengan videos/'],
                        'code' => 400,
                        'status' => 'STATUS_BAD_REQUEST',
                    ]
                ]);
            }

            // Log delete attempt
            $this->logger->info('Video delete attempt', [
                'file_path' => $filePath,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            // Check if file exists
            if (!$this->disk->exists($filePath)) {
                $this->logger->warning('Video delete failed: file not found', [
                    'file_path' => $filePath
                ]);

                return $this->errorResponse([
                    [
                        'title' => 'File tidak ditemukan',
                        'details' => 'File yang diminta tidak ditemukan di penyimpanan',
                        'code' => 404,
                        'status' => 'STATUS_NOT_FOUND',
                    ]
                ]);
            }

            // Delete file from s3 minio
            $deleted = $this->disk->delete($filePath);

            if (!$deleted) {
                throw new \Exception('Gagal menghapus file dari penyimpanan');
            }

            // Log successful deletion
            $this->logger->info('Video deleted successfully', [
                'file_path' => $filePath
            ]);

            return $this->successResponse([
                'title' => 'Berhasil menghapus video',
                'code' => 200,
                'status' => 'STATUS_OK',
                'data' => null,
                'meta' => [
                    'user_id' => $request->user('api')->id,
                    'video_path' => $filePath,
                ]
            ]);

        } catch (\Exception $e) {
            // Log error
            $this->logger->error('Video delete failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new HttpResponseException($this->errorInternalToResponse($e, 'Gagal menghapus video karena kegagalan server'));
        }
    }
}
