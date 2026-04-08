{{--
Regex validion for text field elements in form
--}}

onkeydown="
    const nav=['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End','Tab','Enter'];
    if (nav.includes(event.key) || event.ctrlKey || event.metaKey) return;

    {{-- Allow letters --}}
    if (/^[A-Za-z]$/.test(event.key)) return;

    {{-- Allow a single space between words (no leading or double spaces) --}}
    if (event.key === ' ') {
      const el = event.target;
      const val = el.value;
      const i = el.selectionStart, j = el.selectionEnd;
      const replacing = i !== j;
      const atStart = i === 0;
      const prevCharIsSpace = i > 0 && val[i-1] === ' ';
      if (!atStart && !prevCharIsSpace) return; // allow one space between words
    }

    event.preventDefault();
"
oninput="
    let v = this.value;

    {{-- Remove everything except ASCII letters and spaces --}}
    v = v.replace(/[^A-Za-z\s]/g, '');

    {{--  Collapse multiple spaces to a single space --}}
    v = v.replace(/\s+/g, ' ');

    {{--  Trim leading/trailing spaces --}}
    v = v.trimStart();

    this.value = v;
"
