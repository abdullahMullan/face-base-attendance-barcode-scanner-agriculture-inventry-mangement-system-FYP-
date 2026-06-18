<?php

namespace App\Services;

use App\Models\FaceProfile;

class FaceRecognitionService
{
    public function match(array $descriptor, float $threshold = 0.48): ?array
    {
        $bestMatch = null;

        $profiles = FaceProfile::with('user')
            ->where('is_active', true)
            ->whereHas('user', function ($query): void {
                $query->where('is_active', true);
            })
            ->get();

        foreach ($profiles as $profile) {
            foreach ((array) $profile->descriptors as $candidate) {
                if (! is_array($candidate) || count($candidate) !== count($descriptor)) {
                    continue;
                }

                $distance = $this->distance($descriptor, $candidate);

                if ($bestMatch === null || $distance < $bestMatch['distance']) {
                    $bestMatch = [
                        'profile' => $profile,
                        'user' => $profile->user,
                        'distance' => $distance,
                    ];
                }
            }
        }

        if ($bestMatch === null || $bestMatch['distance'] > $threshold) {
            return null;
        }

        return $bestMatch;
    }

    public function distance(array $left, array $right): float
    {
        $sum = 0.0;

        foreach ($left as $index => $value) {
            $delta = (float) $value - (float) ($right[$index] ?? 0);
            $sum += $delta * $delta;
        }

        return sqrt($sum);
    }
}