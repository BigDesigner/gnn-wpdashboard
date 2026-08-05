# System Coherence & Operational Rules — GNN WPDashboard

This document establishes operational coherence, locking mechanisms, pre-change checklists, and session protocols for GNN WPDashboard.

## Session Protocols
- **Interactive Language**: Interactive messages and chat responses MUST be in Turkish (`tr`).
- **Memory Bank Storage**: All `.memory-bank/`, `.specs/`, `.agents/`, and `.tasks/` files MUST remain in English.
- **Locking Protocol**: Check `.memory-bank/.session.lock` before executing destructive or architectural mutations.

## Pre-Change Checklist
1. Inspect authoritative source files before making code edits.
2. Verify PHP syntax using `php -l` on all modified PHP files.
3. Enforce strict isolation between third-party WordPress plugins and GNN ecosystem plugins.
4. Ensure `gnn-wpdashboard` self-protection guardrails remain unbroken during bulk operations.

## Post-Change Checklist
1. Run syntax verification (`php -l`).
2. Update `.memory-bank/changelog/verified-worklog.md` with completed changes.
3. Update `.tasks/pipeline.md` with active and upcoming tasks.
