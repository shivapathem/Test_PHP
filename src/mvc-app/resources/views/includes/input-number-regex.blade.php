{{--
Regex validion for numeric element in form
--}}

onkeydown="
    {{-- Block E/e, plus, minus, and other non-numeric keys except navigation --}}
    const blocked = ['e','E','+','-','.'];
    if (blocked.includes(event.key)) event.preventDefault();
"
oninput="   
    {{--  Keep only digits (handles paste/IME) --}}
    let v = this.value.replace(/\D+/g, '');

    // If the entire input is zeros, collapse to a single '0'
    if (/^0+$/.test(v)) {
        this.value = '0';
        return;
    }

    {{--  Otherwise, remove leading zeros before non-zero digits --}}
    this.value = v.replace(/^0+(?=\d)/, '');
"