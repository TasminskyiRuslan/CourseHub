<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

use App\Enums\CourseType;
use App\Enums\UserRole;
use Illuminate\Http\UploadedFile;

pest()->extend(Tests\TestCase::class)
    // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Get the expected JSON structure for a user object.
 *
 * @return array
 */
function userJsonStructure(): array {
    return [
        'id',
        'name',
        'slug',
        'email',
        'email_verified_at',
        'role',
    ];
}

/**
 * Get the expected JSON structure for an authentication response.
 *
 * @return array
 */
function authJsonStructure(): array {
    return [
        'user' => userJsonStructure(),
        'access_token',
        'token_type',
        'expires_at',
    ];
}

/**
 * Get the expected JSON structure for an author object.
 *
 * @return array
 */
function authorJsonStructure(): array {
    return [
        'id',
        'name',
        'slug',
    ];
}

/**
 * Get the expected JSON structure for a lesson object.
 *
 * @param CourseType|null $courseType
 * @return array
 */
function lessonJsonStructure(?CourseType $courseType = null): array {
    return [
        'id',
        'course_id',
        'title',
        'slug',
        'position',
        'content' => match ($courseType) {
            CourseType::OFFLINE => [
                'start_time',
                'end_time',
                'address',
                'room_number'
            ],
            CourseType::ONLINE => [
                'start_time',
                'end_time',
                'meeting_link',
            ],
            CourseType::VIDEO => [
                'video_url',
                'provider',
            ],
            null => [],
        },
        'created_at',
        'updated_at',
    ];
}

/**
 * Get the expected JSON structure for a course object.
 *
 * @param bool $withAuthor
 * @param bool $withLessonsCount
 * @param bool $withLessons
 * @param CourseType|null $courseType
 * @return array
 */
function courseJsonStructure(bool $withAuthor = false, bool $withLessonsCount = false, bool $withLessons = false, ?CourseType $courseType = null): array {
    $base = [
        'id',
        'author_id',
        'title',
        'slug',
        'description',
        'type',
        'price',
        'image_url',
        'is_published',
        'created_at',
        'updated_at',
    ];
    if ($withAuthor) {
        $base['author'] = authorJsonStructure();
    }
    if ($withLessonsCount) {
        $base[] = 'lessons_count';
    }
    if ($withLessons) {
        $base['lessons'] = [
            '*' => lessonJsonStructure($courseType),
        ];;
    }
    return $base;
}

/**
 * Get the expected JSON structure for a pagination data.
 *
 * @return array
 */
function paginationJsonStructure(): array {
    return [
        'data',
        'links',
        'meta'
    ];
}

/**
 * Generate a registration payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'name'     => fake()->name(),
        'email'    => fake()->unique()->safeEmail(),
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => UserRole::STUDENT->value,
    ], $overrides);
}

/**
 * Generate a creation course payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function creatingCoursePayload(array $overrides = []): array
{
    return array_merge([
        'title' => fake()->sentence(3),
        'description' => fake()->optional()->paragraph(),
        'type' => CourseType::OFFLINE,
        'price' => (string) fake()->randomFloat(2, 0, 99999999.99),
    ], $overrides);
}

/**
 * Generate an updating course payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function updatingCoursePayload(array $overrides = []): array
{
    return array_merge([
        'title' => fake()->sentence(3),
        'description' => fake()->optional()->paragraph(),
        'price' => (string) fake()->randomFloat(2, 0, 99999999.99),
    ], $overrides);
}

/**
 * Generate an image payload with optional overrides.
 *
 * @param array $overrides
 * @return array
 */
function imagePayload(array $overrides = []): array
{
    return array_merge([
        'image'     => UploadedFile::fake()->image('image.jpg'),
        '_method' => 'PUT',
    ], $overrides);
}
