<?php

namespace Tests\Feature;

use App\Http\Requests\Admin\Auto\StoreAutoRequest;
use App\Http\Requests\Admin\Auto\UpdateAutoRequest;
use App\Http\Requests\Autos\CreateRequest;
use App\Models\Auto;
use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class UploadFileSizeValidationTest extends TestCase
{
    #[DataProvider('uploadRuleProvider')]
    public function test_upload_rules_allow_exactly_50mb(Closure $requestFactory, string $field, string $fileName, string $mimeType): void
    {
        $request = $requestFactory();
        $rules = $this->extractRulesForField($request->rules(), $field);
        $file = UploadedFile::fake()->create($fileName, 51200, $mimeType);

        $validator = Validator::make([$field => [$file]], $rules);

        $this->assertTrue($validator->passes(), json_encode($validator->errors()->toArray(), JSON_THROW_ON_ERROR));
    }

    #[DataProvider('uploadRuleProvider')]
    public function test_upload_rules_reject_files_larger_than_50mb(Closure $requestFactory, string $field, string $fileName, string $mimeType): void
    {
        $request = $requestFactory();
        $rules = $this->extractRulesForField($request->rules(), $field);
        $file = UploadedFile::fake()->create($fileName, 51201, $mimeType);

        $validator = Validator::make([$field => [$file]], $rules);

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey($field . '.0', $validator->errors()->toArray());
    }

    public static function uploadRuleProvider(): array
    {
        return [
            'client photos' => [self::createRequestFactory(CreateRequest::class), 'photos', 'photo.jpg', 'image/jpeg'],
            'client videos' => [self::createRequestFactory(CreateRequest::class), 'videos', 'video.mp4', 'video/mp4'],
            'client documents' => [self::createRequestFactory(CreateRequest::class), 'documents', 'doc.pdf', 'application/pdf'],
            'admin store photos' => [self::createRequestFactory(StoreAutoRequest::class), 'photos', 'photo.jpg', 'image/jpeg'],
            'admin store videos' => [self::createRequestFactory(StoreAutoRequest::class), 'videos', 'video.mp4', 'video/mp4'],
            'admin store documents' => [self::createRequestFactory(StoreAutoRequest::class), 'documents', 'doc.pdf', 'application/pdf'],
            'admin update photos' => [self::createRequestFactory(UpdateAutoRequest::class), 'photos', 'photo.jpg', 'image/jpeg'],
            'admin update videos' => [self::createRequestFactory(UpdateAutoRequest::class), 'videos', 'video.mp4', 'video/mp4'],
            'admin update documents' => [self::createRequestFactory(UpdateAutoRequest::class), 'documents', 'doc.pdf', 'application/pdf'],
        ];
    }

    /**
     * @param class-string<CreateRequest|StoreAutoRequest|UpdateAutoRequest> $className
     */
    private static function createRequestFactory(string $className): Closure
    {
        return static function () use ($className) {
            $request = new $className();

            if ($request instanceof UpdateAutoRequest) {
                $request->setRouteResolver(static function () {
                    return new class {
                        public function parameter(string $key, mixed $default = null): mixed
                        {
                            if ($key === 'auto') {
                                $auto = new Auto();
                                $auto->id = 1;

                                return $auto;
                            }

                            return $default;
                        }
                    };
                });
            }

            return $request;
        };
    }

    /**
     * @param array<string, mixed> $allRules
     * @return array<string, mixed>
     */
    private function extractRulesForField(array $allRules, string $field): array
    {
        $rules = [$field . '.*' => $allRules[$field . '.*'] ?? []];

        if (array_key_exists($field, $allRules)) {
            $rules[$field] = $allRules[$field];
        }

        return $rules;
    }
}
