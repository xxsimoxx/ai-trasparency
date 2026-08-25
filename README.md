# AI Transparency

AI Transparency is a ClassicPress plugin that adds a visual disclosure badge to images that have been generated or manipulated using artificial intelligence.

The plugin associates AI information with media attachments and can display an AI disclosure badge automatically on the frontend.

## Features

- Mark an image as:
  - **AI generated**
  - **AI manipulated**
  - **AI generated and manipulated**
- Configure the AI disclosure behavior for each image:
  - **Automatic**
  - **Required**
  - **Disabled**
- Display a visual AI badge on disclosed images.
- Provide an accessible screen-reader description.
- Use CSS to customize the appearance and color of the AI badge.
- Keep the AI classification associated with the WordPress media attachment.

## Usage

### 1. Classify an image

Go to **Media → Library** and edit an image.

You will find an **AI content** field with the following options:

- **Not AI-generated or manipulated**
- **AI generated**
- **AI manipulated**
- **AI generated and manipulated**

Select the appropriate value and save the attachment.

### 2. Configure the disclosure

The **AI disclosure** field controls whether the visual disclosure is displayed.

Available options:

#### Automatic

The plugin determines whether a disclosure should be displayed.

The current implementation deliberately does not force a disclosure in automatic mode. The automatic rules can be extended independently in the future.

#### Required

Always display the AI disclosure badge for the image.

#### Disabled

Do not display the AI disclosure badge for the image, even if the image has an AI classification.

### 3. Display on the frontend

When disclosure is required, the plugin wraps the image with markup similar to:

```html
<span class="ai-content ai-logo ai-generated">
    <span class="screen-reader-text">
        Content generated with artificial intelligence
    </span>

    <img src="..." alt="...">
</span>
```

The exact HTML may depend on how the image is rendered by WordPress or the theme.

The plugin currently handles images generated through `wp_get_attachment_image()` and images found in post/page content.

## CSS customization

The AI badge can be customized using CSS.

The main class for customizing the logo is:

```css
.ai-logo
```

### Change the logo color

Add the following CSS to your theme's custom CSS:

```css
.ai-logo {
    color: #0066cc;
}
```

For example, to use a dark gray:

```css
.ai-logo {
    color: #333333;
}
```

Or white:

```css
.ai-logo {
    color: #ffffff;
}
```

The SVG uses `currentColor`, so changing the `color` property changes the color of the logo without modifying the SVG file.

### Different colors for different AI classifications

The plugin also adds a class corresponding to the AI classification.

You can therefore use different colors for each type:

```css
.ai-generated {
    color: #7c3aed;
}

.ai-manipulated {
    color: #2563eb;
}

.ai-generated-and-manipulated {
    color: #dc2626;
}
```

You can also combine the classes explicitly if you want to avoid affecting other elements:

```css
.ai-logo.ai-generated {
    color: #7c3aed;
}

.ai-logo.ai-manipulated {
    color: #2563eb;
}

.ai-logo.ai-generated-and-manipulated {
    color: #dc2626;
}
```

The latter form is recommended.

## Changing the badge size

The default badge size is controlled by the `width` and `height` properties of `.ai-content::after`.

For example:

```css
.ai-content::after {
    width: 32px;
    height: 32px;
}
```

You can use relative units if preferred:

```css
.ai-content::after {
    width: 2.5rem;
    height: 2.5rem;
}
```

### Changing the badge position

The default position is the bottom-right corner:

```css
.ai-content::after {
    right: 0.5rem;
    bottom: 0.5rem;
}
```

For example, to move it to the top-right:

```css
.ai-content::after {
    top: 0.5rem;
    right: 0.5rem;
    bottom: auto;
}
```

## CSS classes

The plugin uses the following classes:

| Class | Purpose |
|---|---|
| `.ai-content` | Main wrapper around a disclosed image |
| `.ai-logo` | Identifies the AI disclosure badge |
| `.ai-generated` | Image was generated using AI |
| `.ai-manipulated` | Image was manipulated using AI |
| `.ai-generated-and-manipulated` | Image was both generated and manipulated using AI |

These classes are intentionally exposed so themes and plugins can customize the appearance without modifying the plugin itself.

## Accessibility

The plugin adds a visually hidden text description to the disclosed image.

For example:

```html
<span class="screen-reader-text">
    Content generated with artificial intelligence
</span>
```

This provides a textual description for users who cannot see the visual badge.

The AI badge itself is decorative and does not rely solely on color to communicate its meaning.

## SVG logo

The AI logo is stored in:

```text
assets/images/ai-logo.svg
```

The SVG is monochromatic and uses:

```xml
currentColor
```

for its visible elements.

The frontend currently uses the SVG as a CSS mask. This allows the logo color to be controlled through the `.ai-logo` CSS class without modifying the SVG.

If you replace the SVG, keep it monochromatic and ensure that the visible shape works correctly as a mask.

## Customizing the logo with a theme

A theme can override the default appearance without modifying the plugin.

For example:

```css
.ai-logo {
    color: #222;
}

.ai-content::after {
    width: 2rem;
    height: 2rem;
    right: 0.75rem;
    bottom: 0.75rem;
}
```

This CSS can be added using:

**Appearance → Customize → Additional CSS**

or through the theme's stylesheet, depending on the theme.
