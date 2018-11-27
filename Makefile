# Example: make release as=minor
.PHONY: release
release:
	standard-version -r $(as) -i changelog.md
