{{--
Regex validion for telephone numeric element in form
--}}

onkeydown="
    const nav=['Backspace','Delete','ArrowLeft','ArrowRight','ArrowUp','ArrowDown','Home','End','Tab','Enter'];

    if (nav.includes(event.key) || event.ctrlKey || event.metaKey) return;

    const el=event.target, val=el.value, s=el.selectionStart, e=el.selectionEnd;

    {{-- digits --}}
    if (event.key>='0' && event.key<='9') return;

    {{-- space/hyphen: not allowed at start unless replacing from start --}}
    if (event.key===' ' || event.key==='-') {
        if ((val.length===0 || s===0) && !(s===0 && e>0)) { event.preventDefault(); }
        return;
    }

    {{-- '+' only once, only at start (or replacing leading selection) --}}
    if (event.key==='+') {
        const hasPlus=val.indexOf('+')!==-1;

        if ((!hasPlus && s===0) || (s===0 && e>0 && val.startsWith('+'))) return;
        event.preventDefault();
        return;
    }

    {{-- block everything else --}}
    event.preventDefault();
"
oninput="
    let v=this.value;

    {{-- fast allow-list filter --}}
    v=v.replace(/[^+\d \-]/g,'');

    {{-- keep a single leading '+' --}}
    if (v[0]==='+') {
        v='+'+v.slice(1).replace(/\+/g,'');
    } else {
        v=v.replace(/\+/g,'');
    }

    {{-- drop leading space/hyphen --}}
    v=v.replace(/^[ \-]+/,'');
      
    {{-- if '+' exists, next char must be a digit; otherwise remove '+' --}}
    if (v.startsWith('+') && v.length>1 && v[1]<'0' || v[1]>'9') {
        v=v.slice(1);
    }

    {{-- normalize repeats: collapse spaces and hyphens --}}
    v=v.replace(/ +/g,' ').replace(/\-+/g,'-');

    {{-- cap digits to 15 while typing (ignore non-digits) --}}
    const digits=v.replace(/\D/g,'');
    if (digits.length>15) {
        v=(v.startsWith('+')?'+':'')+digits.slice(0,15);
    }

    this.value=v;
"
 
