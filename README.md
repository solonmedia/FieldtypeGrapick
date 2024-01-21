# FieldtypeGrapick

The FieldtypeGrapick module for ProcessWire wraps the Grapick vanilla javascript gradient creation UI and extends the feature set to include settings beyond what the original library allowed for.

The original javascript library was written by [Artur Arseniev](https://topmate.io/artur_arseniev).

Aside from requiring ProcessWire 3, the requirements are:

- PHP >= 8.0

[The original javascript project](https://github.com/artf/grapick)

[The original live demo page](https://artf.github.io/grapick)

This module makes use of the Spectrum colorpicker library.

Repeater and RepeaterMatrix items are supported.

There is a gremlin in RepeaterPageArray iteration that causes warnings. I've created an issue for it. It does not impact performance.

# CssGradient Object

The FieldtypeGrapick field value is a CssGradient object.

`$gradient = new CssGradient( $options=[] );`

where $options is a list of properties:

    $options = [
      'style' => 'linear',
      'stops' => 'FFFFFFFF^0|FF000000^100',
      'angle' => '180',
      'origin' => '',
      'size' => '',
    ];

# Properties

The CssGradient style by default is linear, 180deg, with a white opaque stop at 0% and a black opaque stop at 100%.

## style

`$gradient->style`: gives you the dropdown value of the style of gradient. Setting this automatically uses the correct settings for the css function and shape parameter as required.

Possible values:

    'linear' = Linear
    'radial-circle' = Radial Circle
    'radial-ellipse' = Radial Ellipse
    'repeating-linear' = Repeating Linear
    'repeating-radial-circle' = Repeating Circle
    'repeating-radial-ellipse' = Repeating Ellipse
    'conical' = Conical
    'repeating-conical' = Repeating Conical

Any other value defaults to linear.

Depending on the type of gradient selected, origin, angle, and/or size will come into play.

The stops are always used to determine the order of colors and their relative locations according to the limitations of each style.

## origin

`$gradient->origin`: gives you the dropdown value of the origin of the gradient as it applies to radial and conical gradients. The format is X%_Y% if for some reason you want to set a custom X/Y origin.

The dropdown values are typically what I find useful for most applications, but I am open to adding other presets.

    '-100%_-100%' = Far Top Left
    '50%_-100%' = Far Top Center
    '200%_-100%' = Far Top Right
    '-50%_-50%' = Near Top Left
    '50%_-50%' = Near Top Center
    '150%_-50%' = Near Top Right
    'top_left' = Top Left
    'top_center' = Top Center
    'top_right' = Top Right
    '-100%_50%' = Far Middle Left
    '-50%_50%' = Near Middle Left
    'center_left' = Middle Left
    'center_center' = Center
    'center_right' = Middle Right
    '150%_50%' = Near Middle Right
    '200%_50%' = Far Middle Right
    'bottom_left' = Bottom Left
    'bottom_center' = Bottom Center
    'bottom_right' = Bottom Right
    '-50%_150%' = Near Bottom Left
    '50%_150%' = Near Bottom Center
    '150%_150%' = Near Bottom Right
    '-100%_200%' = Far Bottom Left
    '50%_200%' = Far Bottom Center
    '200%_200%' = Far Bottom Right

## angle

`$gradient->angle`: gives you the angle in degrees of the gradient as it applies to conical and linear gradients.

Should be a value between -360 and 360. Measured in degrees.

## size

`$gradient->size`: gives you the size - of what depends on the type of gradient.

For radial ellipse gradients, at applies a size of XX% YY% using the value. So 25 would represent a size of 25% width, 25% height of the container.

For repeating linear, conical and radial gradients, the repeating gradient will apply the percentage stops as a percentage of this value.

In the case of repeating linear gradients, if you have your stops at 0%, 10%, 50% and 100% and your size is 200, the stops in the calculated rule will be at 0px, 20px, 100px and 200px.

For repeating ellipse and conical gradients, a similar calculation is performed, but the units are %, not px.

You can get some crazy tartan backgrounds out of this if you stack your gradients up and are creative with transparencies.

## stops

`$gradient->stops`: gives you the current stop settings in a `AARRGGBB^%%` format, with each stop separated by a '|' character on a single line.

You can role your own gradient ruleset by modiying this property prior to getting the rule.

When using the UI you can also reveal the Stops inputfield and change the stops manually, however you will need to save to see the changes in the UI.

## rule

`$gradient->rule`: gives you the stored rule that is calculated prior to the field value being saved to the database from the UI.

Of course, if you are ignoring the UI altogether and just using the class, you will probably **ALWAYS** want to call `getRule()` rather than use this property.

If you render the field, it will return the rule as a string.

# Methods

The CssGradient has a single public method that is mostly useful when manipulating an instance of the CssGradient class.

## getRule(string $delimiter)

`$gradient->getRule()`: calculates the rule based on the properties or options you have set for the field. This automatically runs if you have set an $options array and populate the rule property, but if you decide later that you need to change the properties of the object, you'll want to manually call it again to recalculate it. For example, if you programmatically change the stops, you will want to run the `getRule()` method rather than just grab the rule property.

If you pass a string, the first character will be used as an ending delimiter.

If you pass an empty string, no ending delimited will appear.

By default, the rule is output with a semicolon.

# Grapick UI

The Grapick UI is relatively straightforward.

Clicking the (x) handle above a gradient stop removes the stop from the gradient calculation. If you remove all the stops, the bar is transparent.

Clicking on the gradient bar sets a stop conveniently set to the color you click on.

Clicking on the colorpicker box below the stop line allows you to select the color and transparency of the stop.

Click and drag the stop line itself to modify the gradient.

Making changes to any of the controls on the field will update the preview and the calculated rule in real-time. You can always cut and paste this rule and use it in your designs elsewhere if you want. Likewise, if you open the Stops inputfield area (which is collapsed by default) you can directly alter the colors and code using a color in an AARRGGBB format and adjust the stop with a number from 0-100.

It's fun to play with - experiment with hard and soft lines, size and origin - many interesting effects are possible.

Do not forget that the alpha slider is also available.

# Examples

    $grk = new CssGradient($options=[
        'style' => 'linear',
        'origin' => '',
        'angle' => 270,
        'stops' => 'FF8345E4^0|FF5A08DB^25|FF2C046B^97|FF000000^100',
        'size' => '',
    ]);
    echo $grk->getRule();

will give you:

    'linear-gradient(270deg, rgba(131, 69, 228, 1) 0%, rgba(90, 8, 219, 1) 25%, rgba(44, 4, 107, 1) 97%, rgba(0, 0, 0, 1) 100%);'

while

    echo $grk->getRule('');

will give you

     'linear-gradient(270deg, rgba(131, 69, 228, 1) 0%, rgba(90, 8, 219, 1) 25%, rgba(44, 4, 107, 1) 97%, rgba(0, 0, 0, 1) 100%)'

and 

    echo $grk->gerRule(',');

will give you

    'linear-gradient(270deg, rgba(131, 69, 228, 1) 0%, rgba(90, 8, 219, 1) 25%, rgba(44, 4, 107, 1) 97%, rgba(0, 0, 0, 1) 100%),'