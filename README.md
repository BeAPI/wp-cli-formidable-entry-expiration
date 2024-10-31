
# wp-cli-formidable-entry-expiration

WP-CLI command to clean Formidable Forms entries, by setting a conservation time limit, before which entries will be deleted.

## Installing

Installing this package requires WP-CLI v0.23.0 or greater. Update to the latest stable release with `wp cli update`.

Once you've done so, you can install this package with `wp package install BeAPI/wp-cli-formidable-entry-expiration`

## Usage

`wp clean-formidable-entries {time}`

Time value must be without spaces.

Exemple :
`wp clean-formidable-entries 6months`

Optional parameters :
`--dry-run`

## Credits

Be API
