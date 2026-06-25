# Frontend Validate

Validate the frontend by running lint and tests. Loop until both pass.

## Steps

### 1. Run tests

Run `make frontend-test`.

If tests fail, identify required fixes:
- **Minor fix** (typo, wrong value, missing import, trivial fixture): apply directly.
- **Major fix** (logic change, interface change, multiple files, behavioral change): present a summary of proposed changes and wait for confirmation before applying.

### 2. Run lint fix

After each round of corrections (or if tests passed on the first try), run `make frontend-lint-fix` to catch any formatting issues introduced by the fixes.

### 3. Loop

Go back to step 1 and repeat until `make frontend-test` exits with 0 and `make frontend-lint-fix` produces no further changes.

### 4. Non-convergence

If after a few iterations lint and tests appear to be conflicting with each other (lint breaks tests, test fixes break lint), stop immediately. Report what is conflicting and why, and ask the user how to proceed. Do not attempt further fixes.
