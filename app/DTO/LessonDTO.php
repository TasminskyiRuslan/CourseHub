<?php

namespace App\DTO;

use App\Http\Requests\Api\StoreLessonRequest;
use DateTimeInterface;

final readonly class LessonDTO
{
    public function __construct(
        public string $title,
        public ?string $slug,
        public ?int $position,
        public ?DateTimeInterface $start_time = null,
        public ?DateTimeInterface $end_time = null,
        public ?string $address = null,
        public ?string $room_number = null,
        public ?string $meeting_link = null,
        public ?string $video_url = null,
        public ?string $provider = null,
    ) {}

    public static function fromRequest(StoreLessonRequest $request): self
    {
        return new self(
            title: $request->string('title')->trim()->toString(),
            slug: $request->string('slug')->trim()->toString() ?: null,
            position: $request->has('position') ? $request->integer('position') : null,
            start_time: $request->date('start_time'),
            end_time:   $request->date('end_time'),
            address:      $request->string('address')->trim()->toString() ?: null,
            room_number:  $request->string('room_number')->trim()->toString() ?: null,
            meeting_link: $request->string('meeting_link')->trim()->toString() ?: null,
            video_url:    $request->string('video_url')->trim()->toString() ?: null,
            provider:     $request->string('provider')->trim()->toString() ?: null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title'       => $this->title,
            'slug'        => $this->slug,
            'position'    => $this->position,
            'start_time'  => $this->start_time,
            'end_time'    => $this->end_time,
            'address'     => $this->address,
            'room_number' => $this->room_number,
            'meeting_link'=> $this->meeting_link,
            'video_url'   => $this->video_url,
            'provider'    => $this->provider,
        ], fn($value) => !is_null($value));
    }
}
