<?php

/**
 * Groq LLM Configuration for Quiz Generation (AI-powered questions)
 * Uses the same Groq API key as login risk assessment
 * Used by BackofficeDocumentsController::generateQuizWithAI()
 */

// Groq API key (same as GROQ_API_KEY in .env)
define('GROQ_QUIZ_API_KEY', getenv('GROQ_API_KEY') ?: '');

// Groq model for quiz generation (fast, accurate for MCQs)
define('GROQ_QUIZ_MODEL', getenv('GROQ_MODEL') ?: 'llama-3.3-70b-versatile');

// Groq API endpoint
define('GROQ_API_ENDPOINT', 'https://api.groq.com/openai/v1/chat/completions');

// Temperature for quiz generation (0.0 = deterministic, 1.0 = creative)
// Use 0.3-0.4 for consistent, fact-based questions
define('GROQ_QUIZ_TEMPERATURE', 0.3);

// Max tokens for quiz response
define('GROQ_QUIZ_MAX_TOKENS', 2000);

// Timeout for Groq API requests (seconds)
define('GROQ_API_TIMEOUT', 60);
