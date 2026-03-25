Prepare a new release for the SPIN Framework.

Steps:
1. Ask the user for the new version number if not provided in $ARGUMENTS.
2. Update the version in all four places:
   - `VERSION` (plain text, one line)
   - `composer.json` → `"version"` field
   - `package.json` → `"version"` field
   - `changelog.md` → add a new `## X.Y.Z` section at the top with a placeholder bullet
3. Show a diff summary of the changes.
4. Ask the user to confirm before committing.
5. Commit with message: "release X.Y.Z"
