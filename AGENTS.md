# Project Agents.md Guide for OpenAI Codex

This `AGENTS.md` file provides guidelines for OpenAI Codex and other AI agents interacting with this codebase, including which directories are safe to read from or write to.

## Project Structure: AI Agent Handling Guidelines

| Directory       | Description                                         | Agent Action         |
|-----------------|-----------------------------------------------------|----------------------|
| `/vendor`       | External plugins; may help understand data sources. | Do not modify        |
| `/assets`       | Images used to embed to create card image           | Do not modify        |
| `/sessions`     | Storage of user sessions and user specific assets   | Do not modify        |

## General Guidance

Agents should focus on the core 