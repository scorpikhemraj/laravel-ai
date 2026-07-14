<?php

namespace App\Ai\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;
use Stringable;

class PlaygroundAgent implements Agent
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return 'You are a helpful and knowledgeable AI assistant. Provide concise, clear, and accurate answers.';
    }
}
