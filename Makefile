# Example: make release as=minor
.PHONY: release
release:
	standard-version -r $(as) -s -i changelog.md
