# Animated 3D Blob

A lightweight, standalone animated 3D blob that runs directly in a web browser. Its appearance and animation can be customized through a configuration file and a password-protected web editor.

## Overview

This project began as an effort to create a simple, animated blob inspired by the *AI Assistant Blob* experiment by Aaron Iker. The objective was to retain the original's organic, continuously changing 3D appearance while making it easy to deploy on a personal website and customize without editing JavaScript.

The resulting project separates the rendering code from its configuration. Colors and animation parameters are stored in a JSON file, while a small HTML editor provides color pickers and sliders for changing those settings. A PHP endpoint saves changes on the server so they persist between visits and browser sessions.

No database is required.

## Features

- Continuously animated, three-dimensional organic shape
- WebGL rendering with Three.js
- Procedural shape deformation using Simplex noise
- Adjustable blob and lighting colors
- Adjustable animation speed, shape irregularity and processing factor
- Separate JSON configuration file
- Browser-based configuration editor with color pickers and sliders
- Persistent server-side settings
- Password-protected editor and save endpoint
- No build system or package manager required
- Libraries can be hosted locally, without relying on external CDNs

## Screenshot

![Animated 3D Blob](https://github.com/user-attachments/assets/6453f9ec-4ddd-476d-8233-198dc154dd13)

## How it works

The project uses three main components.

### 1. Rendering

`script.js` creates a Three.js scene containing a high-resolution sphere. The sphere's vertices are continuously displaced using Simplex noise, creating the blob's organic movement.

Three.js handles the 3D geometry, materials, lighting and rendering. The animation runs through `requestAnimationFrame()`.

### 2. Configuration

`blob-config.json` stores the colors and animation parameters. When the page loads, `script.js` retrieves this file and uses its values to initialize the scene.

Because the configuration is separate from the rendering code, changing settings does not require modifying `script.js`.

### 3. Configuration editor

`editor/config.html` provides a user interface for editing the settings:

- Color pickers display the selected hexadecimal color codes.
- Sliders adjust the numeric animation parameters and display their current values and permitted ranges.
- **Save settings** sends the new configuration to `editor/save.php`.
- **Reset defaults** restores the default settings defined by the editor.

The PHP endpoint validates the submitted values before writing them to `blob-config.json`. The editor directory is protected using HTTP authentication configured through the hosting control panel.

The configuration file itself is publicly readable, but only authenticated users should be able to modify it through the provided editor.

## Project structure

```text
public_html/
├── index.html
├── style.css
├── script.js
├── blob-config.json
├── lib/
│   ├── jquery-3.4.1.min.js
│   ├── three.min.js
│   └── simplex-noise.min.js
└── editor/
    ├── config.html
    └── save.php
```

The filenames and directory paths above reflect the intended deployment layout.

## Requirements

For the public animation:

- A modern web browser with WebGL support
- A web server capable of serving HTML, CSS, JavaScript and JSON files

For the configuration editor:

- PHP enabled on the server
- Permission for PHP to update `blob-config.json`
- HTTPS enabled
- HTTP authentication protecting the editor directory

The editor uses the PHP endpoint to save settings. The public animation does not require PHP because it reads the JSON file directly.

## Installation

### Step 1: Download or clone the repository

Place the project files in a directory on your computer, or upload them directly to your web hosting account.

### Step 2: Install the JavaScript libraries

The project uses the following libraries:

- [jQuery 3.4.1](https://jquery.com/)
- [Three.js](https://threejs.org/) — version r105, used by the current renderer
- [Simplex Noise 2.4.0](https://github.com/jwagner/simplex-noise.js)

The expected local library filenames are:

```text
lib/jquery-3.4.1.min.js
lib/three.min.js
lib/simplex-noise.min.js
```

The current renderer uses the legacy `Geometry.vertices` API available in Three.js r105. Do not replace it with a modern Three.js release without adapting the geometry code.

### Step 3: Configure the blob

Edit `blob-config.json` to set the initial appearance and animation parameters.

Example:

```json
{
  "blobColor": "#e4ecfa",
  "upperLight": "#a7f8ab",
  "lowerLight": "#b7b7b4",
  "ambientLight": "#013404",
  "speed": 0.5,
  "spikes": 1.75,
  "processing": 1.0
}
```

The available settings are documented below.

### Step 4: Upload the public files

Upload `index.html`, `style.css`, `script.js`, `blob-config.json` and the `lib` directory to your website's document root.

Check that the script paths in `index.html` match the actual filenames and directories.

### Step 5: Configure the editor

Upload `config.html` and `save.php` into a directory named `editor` beneath the document root.

Using your hosting control panel, enable directory password protection for the `editor` directory and create a username and password.

The protection must cover both the HTML editor and the PHP save endpoint. Do not rely on hiding the editor link as a security measure.

### Step 6: Verify file permissions

The PHP process must be able to write to `blob-config.json`.

Use the normal file ownership and permissions provided by your hosting environment. Avoid granting unnecessary write access to other users.

The save endpoint validates the expected color formats and numeric ranges before saving. Keep the PHP endpoint protected and do not remove its validation.

### Step 7: Test the installation

1. Open the public page and confirm that the animation renders.
2. Open `/editor/config.html` and verify that authentication is required.
3. Change a color and a numeric value, then save.
4. Reload the editor and verify that the values persist.
5. Reload the public page and confirm that it uses the updated configuration.
6. Test the Reset defaults button.
7. Verify that unauthenticated requests cannot modify the configuration.

## Configuration reference

The default editor ranges are starting points and can be adjusted in `editor/config.html`. The PHP validation ranges in `editor/save.php` must be updated to match if you change them.

| Setting | Description | Default | Range |
|---|---|---|---|
| `blobColor` | Base color of the blob's material | `#e4ecfa` | Six-digit hexadecimal color |
| `upperLight` | Color of the upper directional light | `#a7f8ab` | Six-digit hexadecimal color |
| `lowerLight` | Color of the lower directional light | `#b7b7b4` | Six-digit hexadecimal color |
| `ambientLight` | Ambient lighting color | `#013404` | Six-digit hexadecimal color |
| `speed` | Rate at which the noise animation evolves | `0.5` | `0`–`2` |
| `spikes` | Spatial scale of the noise affecting the shape | `1.75` | `0.1`–`5` |
| `processing` | Factor influencing noise scale and animation speed | `1.0` | `0.1`–`2` |

The color values affect the material and lighting, so the final appearance depends on their interaction rather than on the base color alone.

The processing factor affects both the spatial noise scale and the animation speed. It is not simply a performance-quality setting.

## Local development and testing

The public animation can be tested through a local HTTP server. The configuration editor additionally requires PHP.

If PHP is installed, start its built-in development server from the project directory:

```bash
php -S localhost:8000
```

Then open:

- Public page: `http://localhost:8000/`
- Editor: `http://localhost:8000/editor/config.html`

The PHP development server is intended for local testing, not production hosting. Local saving also requires appropriate write permissions on the configuration file.

Opening the HTML file directly using a `file://` URL is not a suitable way to test the complete application because browser fetch requests and PHP execution require HTTP serving.

## Security notes

The settings in `blob-config.json` are not secrets and may be publicly readable.

The ability to change those settings is different: the PHP save endpoint modifies a server-side file and must remain protected.

- Protect the entire editor directory using HTTP authentication.
- Use HTTPS when accessing the editor.
- Retain server-side validation of all submitted values.
- Do not make the configuration file unnecessarily writable by other users.
- Back up the configuration before making substantial changes.
- Do not expose passwords, hosting credentials or other secrets in the repository.

The provided design is intended for a small personal website. If the editor is later exposed through a different URL or moved outside the protected directory, its authentication and authorization arrangements must be reviewed.

## Credits and acknowledgements

### Original concept and implementation

The project is based on the *AI Assistant Blob* experiment by **Aaron Iker (@aaroniker)** on CodePen:

- [AI Assistant Blob — CodePen](https://codepen.io/porst17/pen/raNmzLM)

The original experiment uses Three.js, Simplex noise and dynamic deformation of a sphere to produce an organic animated shape.

The visual concept was inspired by the Dribbble design:

- [AI Assistant — Dribbble](https://dribbble.com/shots/6546603-AI-Assistant)

This repository adapts that work into a standalone, configurable web page. The standalone layout, local library setup, JSON-based configuration, and PHP-backed editor and persistence are adaptations made for this project.

Please refer to the original source for the original work and its licensing terms.

## Third-party software

This project depends on third-party JavaScript libraries, which remain the property of their respective authors and are subject to their own licenses.

- [jQuery](https://jquery.com/)
- [Three.js](https://threejs.org/)
- [simplex-noise.js](https://github.com/jwagner/simplex-noise.js)

Retain the applicable license and copyright notices when redistributing these libraries. Before redistributing the original or adapted blob implementation, review the license terms applicable to the original CodePen work as well.

## License

The license for this repository should be selected after verifying the licensing terms of the original CodePen implementation and the applicable third-party components.

Do not assume that the original implementation is available under an unrestricted open-source license merely because it is publicly viewable.

## Contributions

Suggestions, bug reports and improvements are welcome. Potential areas for improvement include additional configuration options, better accessibility, more animation controls in the editor, and compatibility with newer versions of Three.js.
