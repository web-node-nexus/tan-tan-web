<?php

namespace App\Support;

final class SearchText
{
    /**
     * Each group is the original token plus a catalog spelling fix, if one is close.
     *
     * @return list<list<string>>
     */
    public function groups(string $raw): array
    {
        $raw = mb_strtolower(trim($raw));
        $parts = preg_split('/\s+/u', $raw) ?: [];
        $groups = [];
        foreach ($parts as $part) {
            if (! is_string($part) || mb_strlen($part) < 2) {
                continue;
            }
            $forms = [$part];
            $fix = $this->correct($part);
            if ($fix !== null && $fix !== $part) {
                $forms[] = $fix;
            }
            $groups[] = $forms;
        }

        return $groups;
    }

    public function correct(string $token): ?string
    {
        $token = mb_strtolower($token);
        $length = mb_strlen($token);
        $limit = $length >= 6 ? 2 : ($length >= 4 ? 1 : 0);
        if ($limit === 0) {
            return null;
        }

        $best = null;
        $bestDistance = $limit + 1;
        foreach (CatalogTree::words() as $word) {
            if (abs(mb_strlen($word) - $length) > $limit) {
                continue;
            }
            if ($word === $token) {
                return null;
            }
            $distance = levenshtein($token, $word);
            if ($distance < $bestDistance) {
                $bestDistance = $distance;
                $best = $word;
            }
        }

        return $bestDistance <= $limit ? $best : null;
    }
}
