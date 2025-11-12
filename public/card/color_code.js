function getLuminance(colorCode) {
    var r = parseInt(colorCode.substr(1, 2), 16);
    var g = parseInt(colorCode.substr(3, 2), 16);
    var b = parseInt(colorCode.substr(5, 2), 16);

    var a = [r, g, b].map(function (v) {
        v /= 255;
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
    });

    return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
}

function hashCode(text) {
    var hash = 0;
    if (text.length === 0) {
        return hash.toString(16);
    }

    for (var i = 0; i < text.length; i++) {
        var charCode = text.charCodeAt(i);
        hash = (hash << 5) - hash + charCode;
        hash = hash & hash; // Convert to 32-bit integer
    }

    return Math.abs(hash).toString(16);
}

function textToColorCode(text) {
    // Generate the hash of the text
    var hash = hashCode(text);

    // Convert the hash to a color code
    var colorCode = "#" + hash.substr(0, 6).padStart(6, "0");

    // Calculate the luminance of the color
    var luminance = getLuminance(colorCode);

    // Check if the luminance is less than 0.75
    while (luminance < 0.75) {
        // Increment the hash to generate a different color
        hash = (parseInt(hash, 16) * 37).toString(16);
        colorCode = "#" + hash.substr(0, 6).padStart(6, "0");
        luminance = getLuminance(colorCode);
    }

    // Return the color code with luminance >= 0.75
    return colorCode;
}
