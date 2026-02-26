/** Define global variables */

var globalVoices;

function esperantoToPolish(esperanto) {
    const replacements = [
        { regex: /a/g, replacement: "a" },
        { regex: /b/g, replacement: "b" },
        { regex: /c/g, replacement: "ts" },
        { regex: /ĉ/g, replacement: "cz" },
        { regex: /d/g, replacement: "d" },
        { regex: /e/g, replacement: "e" },
        { regex: /f/g, replacement: "f" },
        { regex: /g/g, replacement: "g" },
        { regex: /ĝ/g, replacement: "dż" },
        { regex: /h/g, replacement: "h" },
        { regex: /ĥ/g, replacement: "ch" },
        { regex: /i/g, replacement: "ij" },
        { regex: /j/g, replacement: "y" },
        { regex: /ĵ/g, replacement: "rz" },
        { regex: /k/g, replacement: "k" },
        { regex: /l/g, replacement: "l" },
        { regex: /m/g, replacement: "m" },
        { regex: /n/g, replacement: "n" },
        { regex: /o/g, replacement: "o" },
        { regex: /p/g, replacement: "p" },
        { regex: /r/g, replacement: "r" },
        { regex: /s/g, replacement: "s" },
        { regex: /ŝ/g, replacement: "sz" },
        { regex: /t/g, replacement: "t" },
        { regex: /u/g, replacement: "u" },
        { regex: /ŭ/g, replacement: "ł" },
        { regex: /v/g, replacement: "w" },
        { regex: /z/g, replacement: "z" },
        { regex: /tsx/g, replacement: "cz" },
        { regex: /gx/g, replacement: "dż" },
        { regex: /hx/g, replacement: "ch" },
        { regex: /yx/g, replacement: "rz" },
        { regex: /sx/g, replacement: "sz" },
        { regex: /ux/g, replacement: "ł" },
        { regex: /atsij/g, replacement: "atssij" },
        { regex: /ide\b/g, replacement: "ijde" },
        { regex: /io\b/g, replacement: "ijo" },
        { regex: /ioy\b/g, replacement: "ijoj" },
        { regex: /ioyn\b/g, replacement: "ijojn" },
        { regex: /feyo\b/g, replacement: "fejo" },
        { regex: /feyoy\b/g, replacement: "feyoj" },
        { regex: /feyoyn\b/g, replacement: "feyoj" },
        { regex: /^ekzij/g, replacement: "ekzji" },
        { regex: /tssijl/g, replacement: "tssil" },
        { regex: /ijuy/g, replacement: "iuyy" },
        { regex: /ijeh/g, replacement: "ije" },
        { regex: /sijlo/g, replacement: "ssilo" },
        { regex: /^sij/g, replacement: "syy" },
        { regex: /tsij/g, replacement: "tssij" },
        { regex: /sij/g, replacement: "ssij" },
        { regex: /sssij/g, replacement: "ssij" },
        { regex: /rijpozij/g, replacement: "ryypozyj" },
        { regex: /zijs/g, replacement: "zyjs" },
        { regex: /\bok\b/g, replacement: "ohk" },
        { regex: /\bs-ro\b/g, replacement: "sjijnjoro" },
        { regex: /\bs-ino\b/g, replacement: "sjijnjorijno" },
        { regex: /\bktp\b/g, replacement: "ko-to-po" },
        { regex: /\bk.t.p\b/g, replacement: "ko-to-po" },
        { regex: /\batm\b/g, replacement: "antałtagmeze" },
        { regex: /\bptm\b/g, replacement: "posttagmeze" },
        { regex: /\bbv\b/g, replacement: "bonvolu" }
    ];

    let result = esperanto;
    replacements.forEach(({ regex, replacement }) => {
        result = result.replace(regex, replacement);
    });

    console.log(esperanto + " -> " + result);
    return result;
}

function getRandomVoice(langCode) {
    // @hack Make subsitutes.
    if (langCode == "eo") {
        langCode = "pl";
    } else if (langCode == "la") {
        langCode = "it";
    }

    // Filter the globalVoices array based on the voice name matching the string 'lang'
    const matchingVoices = globalVoices.filter((voice) => voice.lang.startsWith(langCode));

    if (matchingVoices.length === 0) {
        // No matching voices found
        alert("No matching voices found for " + langCode);
        return null;
    }

    // Select a random index from the matching voices array
    const randomIndex = Math.floor(Math.random() * matchingVoices.length);

    // Return the randomly selected voice
    const voice = matchingVoices[randomIndex];
    console.log(voice.lang + ": " + voice.name);
    return voice;
}

function speak(id, language, callback) {
    var msg = new SpeechSynthesisUtterance();
    var voice = getRandomVoice(language);
    msg.voice = voice;
    msg.volume = 1; // 0 to 1
    var rate = 1.1;
    if (language && language != "en-US") {
        rate = 0.9;
    }
    msg.rate = rate; // 0.1 to 10
    msg.pitch = 1; //0 to 2
    const element = document.getElementById(id);
    var text = element.textContent.replace(/^(Question|Answer): /, "");
    text = text.replace(/ \(.*?\)/g, "");
    text = text.replace(/\s*\/\s*/g, "; ");

    if (language == "eo") {
        text = esperantoToPolish(text);
    }
    msg.text = text;

    var speechTimeout = null; // Variable to store the timeout ID

    // Define the onend event handler
    msg.onend = function () {
        if (callback && typeof callback === "function") {
            callback(); // Call the callback function if provided
        }
    };

    // Clear the timeout if the speak function is called again before speech ends
    clearTimeout(speechTimeout);

    speechSynthesis.speak(msg);
}

/** Define immediate events */

window.speechSynthesis.onvoiceschanged = function () {
    globalVoices = window.speechSynthesis.getVoices();
};
