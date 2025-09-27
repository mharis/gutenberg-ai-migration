# Gutenberg AI Migration

A WordPress plugin to help migrate content from HTML/Avada/Elementor & etc. to Gutenberg with AI.

## Features

- **SlotFill System**: Provides a document settings panel in the Gutenberg editor where other plugins can add their migration tools
- **AI-Powered Migration**: Framework for implementing AI-driven content migration
- **Extensible**: Other plugins can easily extend the migration panel

## Development Setup

1. Install dependencies:
   ```bash
   npm install
   ```

2. Build the JavaScript assets:
   ```bash
   npm run build
   ```

3. For development with watch mode:
   ```bash
   npm start
   ```

## Using the SlotFill

The plugin provides a slotfill system that allows other plugins to add components to the "Gutenberg AI Migration" document settings panel.

### Adding a Fill Component

To add your own component to the migration panel, use the global `GutenbergAIMigrationEditorSettingPanel` function:

```javascript
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const MyMigrationTool = () => {
    return (
        <div>
            <h4>{ __( 'My Migration Tool', 'my-plugin' ) }</h4>
            <Button variant="primary">
                { __( 'Migrate Content', 'my-plugin' ) }
            </Button>
        </div>
    );
};

// Register your component
if ( window.GutenbergAIMigrationEditorSettingPanel ) {
    window.GutenbergAIMigrationEditorSettingPanel( { 
        children: <MyMigrationTool /> 
    } );
}
```

### Slot Name

The slot name used internally is: `gutenberg-ai-migration-editor-setting-panel`

## File Structure

```
src/
├── js/
│   ├── index.js              # Main slotfill component
│   └── example-fill.js       # Example fill component
├── assets/
│   └── img/
│       └── block-icon.svg    # Plugin icon
└── css/                      # Styles (if needed)
```

## Building

The plugin uses `@wordpress/scripts` for building JavaScript assets. The build process creates:

- `build/gutenberg-ai-migration.js` - Main JavaScript file
- `build/gutenberg-ai-migration.asset.php` - Asset dependencies and version info

## Requirements

- WordPress 6.8+
- PHP 8.3+
- Node.js (for development)