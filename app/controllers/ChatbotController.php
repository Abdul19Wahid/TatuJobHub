<?php
/**
 * ChatbotController — AI career assistant endpoint
 * Route: POST /chatbot/message  (no auth required, no CSRF needed)
 */
class ChatbotController
{
    public function message(): void
    {
        // Buffer ALL output so stray PHP warnings don't corrupt the JSON response
        ob_start();

        // Suppress notices/warnings for this endpoint only
        $prevError = error_reporting(0);

        try {
            $raw  = file_get_contents('php://input');
            $body = json_decode($raw, true);
            if (!$body) $body = $_POST;

            $userMessage = trim($body['message'] ?? '');
            $history     = $body['history'] ?? [];

            if (empty($userMessage)) {
                $this->respond(['reply' => 'Please type a message.']);
                return;
            }

            $apiKey = defined('ANTHROPIC_API_KEY') ? ANTHROPIC_API_KEY : '';

            if (empty($apiKey)) {
                // No API key — use smart rule-based fallback
                $this->respond(['reply' => $this->fallbackReply($userMessage)]);
                return;
            }

            // Build messages for Anthropic API
            $messages = [];
            foreach (array_slice($history, -8) as $h) {
                if (isset($h['role'], $h['content']) && in_array($h['role'], ['user','assistant'])) {
                    $messages[] = ['role' => $h['role'], 'content' => substr($h['content'], 0, 500)];
                }
            }
            $last = end($messages);
            if (!$last || $last['content'] !== $userMessage) {
                $messages[] = ['role' => 'user', 'content' => $userMessage];
            }

            $systemPrompt = "You are TatuJobHub's friendly AI career assistant for Ghana's job market. "
                . "Help with: jobs, CVs, interviews, career advice, salary negotiation, and the platform. "
                . "Platform: tatujobhub.xo.je — seekers browse /jobs, employers post jobs. "
                . "Be concise (max 3-4 sentences), warm, and practical. "
                . "Reference Ghana job market context when relevant.";

            $ch = curl_init('https://api.anthropic.com/v1/messages');
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode([
                    'model'      => 'claude-haiku-4-5-20251001',
                    'max_tokens' => 300,
                    'system'     => $systemPrompt,
                    'messages'   => $messages,
                ]),
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'x-api-key: ' . $apiKey,
                    'anthropic-version: 2023-06-01',
                ],
                CURLOPT_TIMEOUT        => 20,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!$response || $httpCode !== 200) {
                $this->respond(['reply' => $this->fallbackReply($userMessage)]);
                return;
            }

            $data  = json_decode($response, true);
            $reply = $data['content'][0]['text'] ?? $this->fallbackReply($userMessage);
            $this->respond(['reply' => $reply]);

        } catch (Throwable $e) {
            error_reporting($prevError);
            ob_end_clean();
            header('Content-Type: application/json');
            echo json_encode(['reply' => $this->fallbackReply($body['message'] ?? '')]);
            exit;
        }
    }

    private function respond(array $data): void
    {
        ob_end_clean(); // discard any stray output (warnings, notices)
        header('Content-Type: application/json');
        header('Cache-Control: no-cache');
        echo json_encode($data);
        exit;
    }

    private function fallbackReply(string $msg): string
    {
        $m = strtolower($msg);

        if (str_contains($m,'cv')||str_contains($m,'resume')) {
            return "A great CV should be 1-2 pages with a professional summary, work experience (with achievements), education, and skills. Upload yours on TatuJobHub at /seeker/profile to apply faster!";
        }
        if (str_contains($m,'interview')) {
            return "Research the company, prepare answers for common questions (\"Tell me about yourself\", \"Why this role?\"), and have your own questions ready. Practice out loud before the day!";
        }
        if (str_contains($m,'salary')||str_contains($m,'pay')||str_contains($m,'ghs')) {
            return "Research market rates in Ghana before negotiating. Don't name a number first if you can avoid it — let the employer lead. Always negotiate; most expect it. Check salary ranges on job listings at /jobs.";
        }
        if (str_contains($m,'find job')||str_contains($m,'looking for')||str_contains($m,'search')||str_contains($m,'job')) {
            return "Browse all current job listings at /jobs. Use filters for industry, job type, location, and experience level. Set a Job Alert so you get emailed when matching jobs are posted!";
        }
        if (str_contains($m,'register')||str_contains($m,'sign up')||str_contains($m,'account')) {
            return "Creating an account is free — visit /register and choose Job Seeker or Employer. Seekers can apply to jobs instantly; employers can post listings and manage applicants.";
        }
        if (str_contains($m,'company')||str_contains($m,'companies')) {
            return "Browse verified companies hiring right now at /companies. Each profile shows open positions, company description, and industry.";
        }
        if (str_contains($m,'hi')||str_contains($m,'hello')||str_contains($m,'hey')) {
            return "Hello! 👋 I'm TatuJobHub's career assistant. I can help you find jobs, polish your CV, prepare for interviews, or answer questions about the platform. What would you like help with?";
        }
        if (str_contains($m,'how')||str_contains($m,'work')) {
            return "TatuJobHub connects job seekers and employers in Ghana. Seekers build a profile, upload a CV, and apply to jobs. Employers post listings and review applications. Start at /jobs to explore!";
        }

        return "Great question! I'd suggest starting by browsing jobs at /jobs, or set up your profile to get personalised recommendations. Is there something specific I can help with — CV tips, interview prep, or finding the right role?";
    }
}
