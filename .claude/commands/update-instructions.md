# Update Instructions

Add or update an instruction, convention, or architectural fact in the right file.

## Objective

Write instructions and docs that generate **deterministic code** while consuming **as few tokens as possible**. This is the arbitration criterion: when two placements are possible, prefer the one that loads fewer tokens and leaves less room for interpretation.

## Policy — file map

| File | Contains |
|---|---|
| Root `CLAUDE.md` | Cross-app instructions only — applies to all 3 apps |
| `apps/*/CLAUDE.md` | Coding conventions and patterns for that specific app |
| `apps/*/docs/architecture.md` | Structural reference: folder structure, layer boundaries, module isolation, routes, types — never implementation patterns |
| `docs/architecture.md` | Cross-service contracts only: request flow, JWT format, injected headers, `apps/user` endpoints |
| `docs/domain.md` | Ubiquitous language: domain concepts, aggregates, value objects |
| `docs/local-dev.md` | Dev environment: ports, bypass mode |
| `docs/adr/` | Architectural decisions — never list individually in other files, reference the folder |
| `.claude/memory/` | Project philosophy and unique context not derivable from code or CLAUDE.md |

## Policy — constraints

- CLAUDE.md files are **always loaded** → keep minimal, only actionable instructions, no narrative
- `docs/*` are loaded **on demand** → reference material, can be more detailed
- **No duplication** — if a rule is in root CLAUDE.md, never repeat it in a sub-app CLAUDE.md
- **architecture.md = "what exists and where"** — structural reference only
- **CLAUDE.md = "how to code"** — instructions and patterns only
- Cross-app rules → root CLAUDE.md. App-specific rules → sub-app CLAUDE.md
- Never list individual ADRs in any file — always reference `docs/adr/` as a directory

## Steps

### 1. Classify

Apply the decision tree:
1. Applies to all 3 apps? → root `CLAUDE.md`
2. Coding convention or pattern for one app? → that app's `CLAUDE.md`
3. Structural reference (folder layout, layer boundaries, routes, types)? → that app's `docs/architecture.md`
4. Cross-service contract (JWT, headers, endpoints, request flow)? → `docs/architecture.md`
5. Domain concept or term? → `docs/domain.md`
6. Dev environment detail? → `docs/local-dev.md`
7. Project philosophy or unique context? → `.claude/memory/`

### 2. Check for duplication

Read the target file. If the information is already present, report it and stop — do not add a duplicate.

### 3. Edit

Add the minimum necessary. No narrative, no explanation of what was added — just the instruction or fact itself, consistent with the style of the surrounding content.

### 4. Report

One sentence: what was added and in which file. If the classification was non-obvious, explain the reasoning in one sentence.
