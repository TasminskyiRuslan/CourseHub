<?php

namespace App\DTO\Lessons;

use App\Http\Requests\Api\Lessons\StoreLessonRequest;
use App\Http\Requests\Api\Lessons\UpdateLessonRequest;
use DateTimeInterface;

final readonly class LessonDTO
{
    public function __construct(
        public string $title,
        public ?string $slug,
        public ?int $position,
        public ?DateTimeInterface $start_time,
        public ?DateTimeInterface $end_time,
        public ?string $address,
        public ?string $room_number,
        public ?string $meeting_link,
        public ?string $video_url,
        public ?string $provider,
    ) {}

    public static function fromRequest(StoreLessonRequest|UpdateLessonRequest $request): self
    {
        return new self(
            title: $request->input('title'),
            slug: $request->input('slug'),
            position: $request->input('position'),
            start_time: $request->date('start_time'),
            end_time:   $request->date('end_time'),
            address:      $request->input('address'),
            room_number:  $request->input('room_number'),
            meeting_link: $request->input('meeting_link'),
            video_url:    $request->input('video_url'),
            provider:     $request->input('provider'),
        );
    }

    public function toArray(): array
    {
        return [
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
        ];
    }
}
