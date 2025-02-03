<?php

namespace App\Http\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class ApiSuccessResponse implements Responsable
{
    public function __construct(
        public readonly mixed $data,
        public readonly string $message,
        public readonly array $metadata = [],
        private readonly int $status = Response::HTTP_OK,
        private readonly array $headers = []
    ) {}

    public function toResponse($request): JsonResponse|Response
    {
        return response()->json(
            data: [
                'success' => true,
                'message' => $this->message,
                'data' => $this->data,
                ...($this->metadata ? ['meta' => $this->metadata] : []),
            ],
            status: $this->status,
            headers: $this->headers
        );
    }
}
