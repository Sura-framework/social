/*
	Javascript library
	Author: Semen Alekseev
*/
(function(){

    var Sura = function(s){
        if(!s) return;
        if(s.sura_object) return s;
        if(Sura.isArray(s)) var elems = s;
        else var elems = typeof s == 'string' ? Sura_Indexer(s) : [s];
        return new Sura_Object({elems: elems, s: s});
    }

    var dom_loaded = false, dom_loaded_cbs = [];

    document.addEventListener('DOMContentLoaded', function(event) {
        dom_loaded = true;
        for(var i = 0; i < dom_loaded_cbs.length; i++) dom_loaded_cbs[i]();
        dom_loaded_cbs = [];
    });

    function Sura_Object(p){
        var num = 0, len = p.elems.length;
        if(len > 0) for(var i = 0; i < len; i++) this[i] = p.elems[i];
        this.length = len;
        this.selector = p.s;
    }
    Sura.fn = Sura_Object.prototype = {
        sura_object: true,
        ready: function(fn, real){
            if(!fn) return;
            if(dom_loaded) {
                if(real) return;
                fn();
            }else dom_loaded_cbs.push(fn);
        },
        css: function(name, value){
            if(typeof name == 'object'){
                for(var i in name) this.css(i, name[i]);
                return this;
            }
            if(arguments.length < 2) return this.getStyle(name);
            if(arguments.length == 2){
                name = substitutionCSS(name);
                for(var i = 0; i < this.length; i++){
                    if(this[i] && this[i].style) this[i].style[name] = value;
                }
            }
            return this;
        },
        getStyle: function(name){
            if(!this.length || !name) return this;
            name = substitutionCSS(name);
            return this[0].style ? String(this[0].style[name]) : '';
        },
        html: function(){
            if(!this.length) return arguments.length ? this : '';
            if(arguments.length) {
                this.empty().append(arguments[0]);
                return this;
            }else{
                if(this.length > 1){
                    var val = [];
                    for(var i = 0; i < this.length; i++) val.push(this[i].innerHTML);
                    return val;
                }else return this[0].innerHTML;
            }
        },
        empty: function(){
            if(!this.length) return this;

            for(var i = 0; i < this.length; i++){
                var el = this[i];

                var childs = getAllEls(el), len = childs.length;
                if(len) {
                    Sura.cleanData(childs);
                    for(var j = 0; j < len; j++) childs[j].parentNode.removeChild(childs[j]);
                }
                el.innerHTML = '';
            }
            return this;
        },
        show: function(){
            for(var i = 0; i < this.length; i++) {
                var el = this[i];
                var old = el.getAttribute('old_display');
                if(!old){
                    if(el.tagName == 'SPAN' || el.tagName == 'INPUT') old = 'inline';
                    else old = 'block';
                }
                el.style['display'] = old;
            }
            return this;
        },
        hide: function(){
            for(var i = 0; i < this.length; i++){
                var old = this[i].style['display'];
                if(old == 'none') old = 'block';
                this[i].style['display'] = 'none';
                this[i].setAttribute('old_display', old);
            }
            return this;
        },
        text: function(){
            if(!this.length) return arguments.length ? this : '';
            if(arguments.length) {
                for(var i = 0; i < this.length; i++) this[i].textContent = arguments[0];
                return this;
            }else{
                if(this.length > 1){
                    var val = [];
                    for(var i = 0; i < this.length; i++) val.push(this[i].textContent);
                    return val;
                }else return this[0].textContent;
            }
        },
        val: function(){
            if(!this.length) return arguments.length == 2 ? '' : this;
            if(arguments.length) {
                for(var i = 0; i < this.length; i++) this[i].value = arguments[0];
                return this;
            }else return this[0].value;
        },
        each: function(object, callback){
            var obj = [];
            for(var i = 0; i < this.length; i++) obj.push(this[i]);
            if(typeof object == 'function') {
                callback = object;
                object = obj;
            }else object = object ? object : obj;
            var name, i = 0, length = object.length;
            if(length === undefined){
                for (name in object) if (callback.call(object[name], name, object[name]) === false) break;
            }else{
                for(var value = object[0]; i < length && callback.call(value, i, value) !== false; value = object[++i]) {}
            }
            return this;
        },
        append: function(html, top){
            var el = Sura.parseHTML(html);
            for(var i = 0; i < this.length; i++) {
                if(top) var first = this[i].firstChild;
                for(var j = 0; j < el.childNodes.length; j++) {
                    var clone = el.childNodes[j].cloneNode(true);
                    if(top) {
                        if(first) this[i].insertBefore(clone, first);
                        else this[i].appendChild(clone);
                    }else this[i].appendChild(clone);
                }
            }
            document.body.appendChild(el);
            Sura(el).remove();
            return this;
        },
        prepend: function(html){
            return this.append(html, 1);
        },
        before: function(html){
            var el = Sura.parseHTML(html);
            for(var i = 0; i < this.length; i++) {
                for(var j = 0; j < el.childNodes.length; j++) {
                    var clone = el.childNodes[j].cloneNode(true);
                    this[i].parentNode.insertBefore(clone, this[i]);
                }
            }
            document.body.appendChild(el);
            Sura(el).remove();
            return this;
        },
        after: function(html){
            var el = Sura.parseHTML(html);
            for(var i = 0; i < this.length; i++) {
                for(var j = 0; j < el.childNodes.length; j++) {
                    var next = this[i].nextSibling, clone = el.childNodes[j].cloneNode(true);
                    if(next) this[i].parentNode.insertBefore(clone, next);
                    else this[i].parentNode.appendChild(clone);
                }
            }
            document.body.appendChild(el);
            Sura(el).remove();
            return this;
        },
        remove: function(){
            if(!this.length) return this;
            for(var i = 0; i < this.length; i++) {
                Sura.cleanData([this[i]]);
                var parent = this[i].parentNode;
                var all_child = getAllEls(this[i]), cl = all_child.length;
                if(cl){
                    Sura.cleanData(all_child);
                    for(var j = 0; j < all_child.length; j++){
                        var cp = all_child[j].parentNode;
                        if(cp) cp.removeChild(all_child[j]);
                    }
                }
                if(parent) parent.removeChild(this[i]);
            }
            return this;
        },
        addClass: function(name){
            if(!name || !this.length) return this;
            var exp = name.split(' ');
            for(var j = 0; j < exp.length; j++){
                for(var i = 0; i < this.length; i++) {
                    if(!this[i]) continue;
                    if(!Sura(this[i]).hasClass(exp[j])) this[i].className = (this[i].className ? this[i].className + ' ' : '') + exp[j];
                }
            }
            return this;
        },
        hasClass: function(name) {
            var has = false;
            if(!this.length) return false;
            for(var i = 0; i < this.length; i++) if((new RegExp('(\\s|^)' + name + '(\\s|$)')).test(this[i].className)) return true;
            return false;
        },
        removeClass: function(name){
            if(!name) return this;
            var exp = name.split(' ');
            for(var j = 0; j < exp.length; j++){
                for(var i = 0; i < this.length; i++) this[i].className = Sura.trim((this[i].className || '').replace((new RegExp('(\\s|^)' + exp[j] + '(\\s|$)')), ' '));
            }
            return this;
        },
        replaceClass: function(oldName, newName){
            this.removeClass(oldName);
            this.addClass(newName);
            return this;
        },
        height: function(){
            if(arguments[0]) return this.css('height', arguments[0]);
            else return this.getSize()[1];
        },
        width: function(){
            if(arguments[0]) return this.css('width', arguments[0]);
            else return this.getSize(1)[0];
        },
        getSize: function(wb){
            if(!this.length) return [0,0];
            var s = [0, 0], de = document.documentElement, elem = this[0], bodyNode = document.body;
            if(elem == document) s =  [Math.max(de.clientWidth, bodyNode.scrollWidth, de.scrollWidth, bodyNode.offsetWidth, de.offsetWidth), Math.max(de.clientHeight, bodyNode.scrollHeight, de.scrollHeight, bodyNode.offsetHeight, de.offsetHeight)];
            else if(elem == window) s = [window.innerWidth, window.innerHeight];
            else {
                if(!elem.style) return [0,0];
                function getSize(){
                    s = [elem.offsetWidth, elem.offsetHeight];
                }
                if(!Sura(elem).isVisible()){
                    var props = {position: 'absolute', visibility: 'hidden', display: 'block'}, old = {};
                    Sura.each(props, function(i, v) {
                        old[i] = elem.style[i];
                        elem.style[i] = v;
                    });
                    getSize();
                    Sura.each(props, function(i, v) {
                        elem.style[i] = old[i];
                    });
                }else getSize();
            }
            return s;
        },
        isVisible: function(){
            var el = this[0];
            if(!el || !el.style) return;
            var display = window.getComputedStyle(el).display;
            return display == 'none' ? false : true;
            //return (el.offsetWidth <= 0 && el.offsetHeight <= 0);
        },
        get: function(index){
            return this[index];
        },
        eq: function(index){
            return this.get(index);
        },
        size: function(){
            return this.length;
        },
        prev: function(){
            if(!this.length) return this;
            var prev = this[0].previousSibling, res = [];
            if(prev) res.push(prev);
            return new Sura_Object({elems: res});
        },
        next: function(){
            if(!this.length) return this;
            var next = this[0].nextSibling;

            while(next && next.nodeType != 1) next = next.nextSibling;

            var res = next ? [next] : [];
            return new Sura_Object({elems: res});
        },
        parent: function(){
            var parent = this[0].parentNode, res = parent ? [parent] : [];
            return new Sura_Object({elems: res});
        },
        parents: function(filters, t){
            var a = this[0], res = [];
            while (a) {
                res.unshift(a);
                a = a.parentNode;
            }
            if(res.length){
                var obj = new Sura_Object({elems: res});
                if(filters) return obj.filter(filters);
                return obj;
            }
            return new Sura_Object({elems: []});
        },
        children: function(selector){
            var res = [];
            if(this.length){
                if(selector){
                    var elems = this[0].querySelectorAll ? this[0].querySelectorAll(selector) : [];
                    res = elems;
                }else{
                    var el = this[0].firstChild, elems = [el];
                    while(el){
                        el = el.nextSibling;
                        if(el && el.nodeType == 1) elems.push(el);
                    }
                    res = elems;
                }
            }
            return new Sura_Object({elems: res});
        },
        attr: function(name, value){
            if(!this.length) return arguments.length == 2 ? this : '';
            if(typeof name == 'object'){
                for(var i in name) this.attr(i, name[i]);
                return this;
            }
            if(arguments.length == 1) return (this[0] && this[0].getAttribute) ? this[0].getAttribute(name) : '';
            if(arguments.length == 2){
                for(var i = 0; i < this.length; i++) {
                    if(value) this[i].setAttribute(name, value);
                    else this[i].removeAttribute(name);
                }
            }
            return this;
        },
        removeAttr: function(name){
            if(this.length && name){
                for(var i = 0; i < this.length; i++){
                    if(this[i].removeAttribute) this[i].removeAttribute(name);
                }
            }
            return this;
        },
        focus: function(){
            if(this[0]) this[0].focus();
            return this;
        },
        autoDir: function(type){
            function onUp(){
                var obj = Sura(this), pattern = new RegExp(/^[\s\u0600-\u06ff]+$/), val = type ? (is_moz ? obj.html() : obj.text()) : obj.val();
                if(val) val = String(val).replace(/\.|\-|\?|\!|\,/g, '').replace(/[0-9]/g, '');
                val = Sura.trim(val);
                if(!val) val = String(obj.attr('placeholder'));
                val = Sura.trim(val);
                if(val.length > 0){
                    val = val.replace(/\.|\-|\?|\!|\,/g, '').replace(/[0-9]/g, '');
                    if(pattern.test(val)){
                        if(!this.rtl){
                            obj.attr('dir', 'rtl');
                            this.rtl = 1;
                        }
                    }else{
                        if(this.rtl){
                            obj.attr('dir', 'ltr');
                            this.rtl = 0;
                        }
                    }
                }else{
                    if(this.rtl){
                        obj.attr('dir', 'ltr');
                        this.rtl = 0;
                    }
                }
            }
            if(this.length){
                var list = [];
                for(var i = 0; i < this.length; i++){
                    var el = this[i];
                    if(!el.finded){
                        el.finded = true;
                        list.push(el);
                    }
                }
                Sura(list).bind('keyup', onUp).each(function(){
                    onUp.apply(this);
                });
            }
            return this;
        },
        bind: function(types, handler, opts){
            if(this.length && types && handler){
                Sura.each(this, function(k, v){
                    Sura.addEvent(v, types, handler, opts);
                });
            }
            return this;
        },
        one: function(type, handler){//no support one
            this.bind(type, handler);
            return this;
        },
        emit: function(type, argv){
            if(this.length){
                var event = new Event(type, {bubbles : true, cancelable : true});
                for(var i = 0; i < this.length; i++){
                    this[i].dispatchEvent(event);
                    //SuraEvents.bind(this[i], {type: type});
                }
            }
            return this;
        },
        trigger: function(type, ev){
            return Sura.each(this, function(){
                var handle = Sura.data(this, 'handle');
                if(handle){
                    var f = function() {
                        handle.call(this, Sura.extend((ev || {}), {type: type, target: this}));
                    };
                    setTimeout(f, 0);
                }
            });
        },
        change: function(){
            this.emit('change');
            return this;
        },
        //load: function(){
        //	this.emit('load');
        //	return this;
        //},
        unbind: function(types, handler){
            if(this.length){
                for(var f = 0; f < this.length; f++){
                    var el = this[f];
                    if(!el) continue;

                    var events = Sura.data(el, 'events');
                    if(!events) continue;

                    if(!types){
                        var cur_types = Object.keys(events);
                        cur_types = cur_types.join(' ');
                    }else var cur_types = types;

                    Sura.each(cur_types.split(/\s+/), function(index, type) {
                        if (!Sura.isArray(events[type])) return;
                        var len = events[type].length;
                        if (Sura.isFunction(handler)) {
                            for(var i = len - 1; i >= 0; i--){
                                if(events[type][i] && (events[type][i] === handler || events[type][i].handler === handler)) {
                                    events[type].splice(i, 1);
                                    len--;
                                    break;
                                }
                            }
                        }else{
                            for (var i = 0; i < len; i++) delete events[type][i];
                            len = 0;
                        }
                        if(!len) {
                            if(el.removeEventListener) el.removeEventListener(type, Sura.data(el, 'handle'), false);
                            else if(el.detachEvent) el.detachEvent('on' + type, Sura.data(el, 'handle'));
                            delete events[type];
                        }
                    });
                    if(Sura.isEmpty(events)) {
                        Sura.removeData(el, 'events')
                        Sura.removeData(el, 'handle')
                    }
                }
            }
            return this;
        },
        htmlToVal: function(){
            if(this.length){
                var cont = this[0];
                var el = cont.firstChild;
                var v = '', contTag = new RegExp('^(DIV|P|LI|OL|TR|TD|BLOCKQUOTE)$');
                while (el) {
                    switch (el.nodeType) {
                        case 3:
                            var str = el.data.replace(/^\n|\n$/g, ' ').replace(/[\n\xa0]/g, ' ').replace(/[ ]+/g, ' ');
                            v += str;
                            break;
                        case 1:
                            var str = Sura(el).htmlToVal();
                            if (el.tagName && el.tagName.match(contTag) && str) {
                                if (str.substr(-1) != '\n') str += '\n';
                                var prev = el.previousSibling;
                                while(prev && prev.nodeType == 3 && Sura.trim(prev.nodeValue) == '')  prev = prev.previousSibling;
                                if (prev && !(prev.tagName && prev.tagName.match(contTag))) str = '\n' + str;
                            } else if (el.tagName == 'IMG'){
                                str += el.getAttribute('emoji_char');
                            }else if (el.tagName == 'BR') str += '\n';
                            v += str;
                            break;
                    }
                    el = el.nextSibling;
                }
                return v;
            }
            return '';
        },
        index: function(elem){
            if(!this.length) return -1;

            if(elem){
                for(var i = 0; i < this.length; i++) if(this[i] == elem) return i;
            }else{
                elem = this[0];
                var parent = elem.parentNode;
                if(!parent) return -1;
                var childs = parent.childNodes;
                for(var i = 0; i < childs.length; i++) if(childs[i] == elem) return i;
            }
            return -1;
        },
        position: function(){
            var el = this[0];
            if(!el) return;
            return {top: el.offsetTop, left: el.offsetLeft};
        },
        //поизиция элемента this[0] относительно элемента node
        positionOf: function(node){
            if(typeof node == 'string') node = document.getElementById(node);

            if(node == window || node == document) return this.offset();

            var top = 0, left = 0;
            if(node){
                var el = this[0];
                while(el){
                    if(el == node) break;
                    if(el.nodeType == 1){
                        top += el.offsetTop;
                        left += el.offsetLeft;
                    }
                    el = el.parentNode;
                }
            }
            return {top: top, left: left};
        },
        offset: function(parent){
            if(this.length){

                var elem = this[0], box = elem.getBoundingClientRect(), body = document.body, docElem = document.documentElement;

                if(parent){
                    var scrollTop = parent.scrollTop, scrollLeft = parent.scrollLeft;
                    var clientTop = parent.clientTop, clientLeft = parent.clientLeft;
                }else{
                    var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop, scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
                    var clientTop = docElem.clientTop || body.clientTop || 0, clientLeft = docElem.clientLeft || body.clientLeft || 0;
                }

                /*var elem = this[0], box = elem.getBoundingClientRect(), body = document.body, docElem = document.documentElement;
                var scrollTop = window.pageYOffset || docElem.scrollTop || body.scrollTop, scrollLeft = window.pageXOffset || docElem.scrollLeft || body.scrollLeft;
                var clientTop = docElem.clientTop || body.clientTop || 0, clientLeft = docElem.clientLeft || body.clientLeft || 0;*/
                var top  = box.top +  scrollTop - clientTop, left = box.left + scrollLeft - clientLeft;
                return {left: Math.round(left), top: Math.round(top)};
            }else return {left: 0, top: 0};
        },
        click: function(fn){
            if(fn) this.bind('click', fn);
            else{
                Sura.each(this, function(){
                    this.click();
                });
            }
            return this;
        },
        keyup: function(fn){
            if(fn) return this.bind('keyup', fn);
            return this.emit('keyup');
        },
        mouseover: function(fn){
            if(fn) return this.bind('mouseover', fn);
            return this.emit('mouseover');
        },
        blur: function(fn){
            if(fn) return this.bind('blur', fn);
            return this.emit('blur');
        },
        scroll: function(fn){
            this.bind('scroll', fn);
            return this;
        },
        resize: function(fn){
            this.bind('resize', fn);
            return this;
        },
        load: function(url, query, callback){
            if(!this.length || !url) return this;
            if(!query) query = {};
            var _s = this;
            Sura.post(url, query, function(d){
                Sura.each(_s, function(){
                    this.innerHTML = d;
                });
                if(callback) callback();
            });
            return this;
        },
        filter: function(selector, find){
            var res = [];
            if(this.length > 0 && selector){
                var filters = [];
                var exp = selector.split(','), len = exp.length;
                for(var i = 0; i < len; i++){
                    var filter = Sura.trim(exp[i]);
                    filters.push(filter);
                }
                var elems = [];
                for(var i = 0; i < this.length; i++){
                    elems.push(this[i]);
                    if(!find) continue;
                    var childs = this[i].querySelectorAll('*');
                    if(childs) Sura.extend(elems, childs);
                }
                for(var i = 0; i < elems.length; i++){
                    var id = elems[i].id, class_name = elems[i].className, tag = elems[i].tagName ? elems[i].tagName.toLowerCase() : '';
                    for(var j = 0; j < len; j++){
                        if(!filters[j]) continue;
                        if(filters[j].substr(0, 1) == '#'){
                            if(filters[j].substr(1) == id){
                                res.push(elems[i]);
                                break;
                            }
                        }else if(filters[j].substr(0, 1) == '.'){
                            if(class_name && (new RegExp('(\\s|^)' + filters[j].substr(1) + '(\\s|$)')).test(class_name)){
                                res.push(elems[i]);
                                break;
                            }
                        }else{
                            if(filters[j].toLowerCase() == tag){
                                res.push(elems[i]);
                                break;
                            }
                        }
                    }
                }
            }
            return new Sura_Object({elems: res});
        },
        find: function(selector){
            return this.filter(selector, 1);
        },
        scrollTop: function(){
            if(!this.length) return false;
            if(arguments.length > 0){
                this[0].scrollTop = arguments[0];
                return this;
            }
            return this[0] == window ? window.scrollY : this[0].scrollTop;
        },
        scrollheight: function(){
            if(!this.length) return false;
            return this[0].scrollHeight;
        },
        scrollWidth: function(){
            if(!this.length) return false;
            return this[0].scrollWidth;
        },
        scrollTopInterval: false,
        anim_timers: {},
        animate: function(obj, time, cb){
            if(!this.length) return this;
            for(var j = 0; j <= this.length; j++){
                var el = this[j];
                if(!el || el.nodeType != 1) continue;
                for(var i in obj){
                    if(i == 'scrollTop') var start = el.scrollTop;
                    else {
                        var start = (el.style && el.style[i]) ? parseInt(el.style[i].replace('px', '')) : 0;
                        if(!start && window.getComputedStyle){
                            start = window.getComputedStyle(el)[i] || 0;
                            if(start) start = parseInt(String(start).replace('px', ''));
                        }
                    }

                    if(start == 0){
                        if(i == 'top') start = el.offsetTop;
                        else if(i == 'left') start = el.offsetLeft;
                    }

                    var end = parseInt(String(obj[i]).replace('px', '')) || 0;

                    if(String(end).indexOf('+=') != -1) end = start+(parseInt(end.replace('+=', '')));
                    else if(String(end).indexOf('-=') != -1) end = start-(parseInt(end.replace('-=', '')));

                    animateProp(el, {
                        start: start,
                        end: end,
                        duration: time,
                        prop: i,
                        complete: cb ? cb : function(){ },
                        delta: i == 'scrollTop' ? animateDelta.quint : 0
                    });
                }
            }
            return this;
        },
        stop: function(){
            clearInterval(this.scrollTopInterval);
            for(var i in this.anim_timers) clearTimeout(this.anim_timers[i]);
            return this;
        },
        fadeIn: function(time){
            if(!this[0]) return;
            if(time == 'slow' || time == 'fast' || !time) time = 200;
            var el = this[0];
            el.style.display = 'block';
            animateProp(el, {
                start: parseInt(el.style.opacity) || 0,
                end: 1,
                duration: time,
                prop: 'opacity'
            });
            return this;
        },
        fadeOut: function(time, cb){
            if(!this[0]) return;
            if(time == 'slow' || time == 'fast' || !time) time = 200;
            var el = this[0];
            animateProp(el, {
                start: parseInt(el.style.opacity) || 0,
                end: 0,
                duration: time,
                prop: 'opacity',
                complete: function(){
                    el.style.display = 'none';
                    if(cb) cb();
                }
            });
            return this;
        },
        keydown: function(fn){
            this.bind('keydown', fn);
            return this;
        },
        mousedown: function(fn){
            this.bind('mousedown', fn);
            return this;
        },
        autoResize: function(max){
            this.bind('keydown keyup', function(){
                var el = Sura(this), min = el.attr('data-min');
                if(!min){
                    min = el.height();
                    el.attr('data-min', min);
                }
                el.css('height', '0px');
                var h = Math.max(min, max ? Math.min(el.scrollheight()+5, max) : el.scrollheight()+5);
                var overflow = h == max ? 'auto' : 'hidden';
                el.css({height: h+'px', 'overflow-y': overflow});
            });
            return this;
        },
        mouseout: function(fn){
            this.bind('mouseout', fn);
        },
        imgAreaSelect: function(){

        },
        submit: function(){
            if(this.length) this[0].submit();
            return this;
        },
        last: function(){
            var res = this.length ? [this[this.length-1]] : [];
            return new Sura_Object({elems: res});
        },
        first: function(){
            var res = this.length ? [this[0]] : [];
            return new Sura_Object({elems: res});
        },
        appendTo: function(selector){
            var parents = typeof selector == 'string' ? Sura(selector) : selector;
            if(!parents.length || !this.length) return;

            for(var i = 0; i < parents.length; i++){
                for(var j = 0; j < this.length; j++) parents[i].appendChild(this[j]);
            }
            return this;
        }
    };

    Sura.parseHTML = function(html){
        var el = document.createElement('div');
        el.innerHTML = html;
        return el;
    }
    Sura.each = function(obj, callback){
        if(typeof obj == 'object' && obj.elems) return Sura.fn.each(obj.elems, callback);
        else return Sura.fn.each(obj, callback);
    };
    Sura.trim = function(text) {
        try{
            return text.trim();
        }catch(e){
            return (text || '').replace(/^\s+|\s+$/g, '');
        }
    };

    Sura.cleanData = function(els){
        for(var i = 0; i < els.length; i++){
            var el = els[i], obj = Sura(el);
            obj.unbind();

            var tweens = Sura.data(el, 'tweens');
            if(tweens) for(var j in tweens) clearInterval(tweens[j]);

            Sura.removeData(el);
            obj.removeAttr('onmouseover');
            obj.removeAttr('onmouseout');
            obj.removeAttr('onmousedown');
            obj.removeAttr('onmouseup');
            obj.removeAttr('onClick');
            if(el.tt) el.tt.destroy();
        }
    };

    function ajaxGetObject(){
        try {
            this.a = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            try {
                this.a = new ActiveXObject("Microsoft.XMLHTTP");
            } catch (E) {
                this.a = false;
            }
        }
        if (!this.a && typeof XMLHttpRequest != 'undefined') {
            this.a = new XMLHttpRequest();
        }
        return this.a;
    }
    function suraAjax(url, opts){
        this.xhr = ajaxGetObject();

        if(url.substr(0, 1) != '/' && url.substr(0, 5) != 'http:' && url.substr(0, 6) != 'https:') url = '/'+url;
        this.url = url;

        if(!opts) opts = {};
        if(!opts.method) opts.method = 'POST';
        if(!opts.data && !opts.nocache) opts.data = {_ts: Sura.now()};

        this.method = opts.method;
        this.data = opts.data;
        if(opts.data){
            if(opts.method != 'POST') this.url += (url.indexOf('?') != -1 ? '&' : '?')+this.make_query();
        }

        if(opts.done) this.onDone = opts.done;
        if(opts.fail) this.onFail = opts.fail;

        if(opts.toJson) this.toJson = true;

        return this.request();
    }
    suraAjax.prototype.make_query = function(){
        if(this.data){
            var data = [];
            for(var i in this.data) data.push(i+'='+encodeURIComponent(this.data[i]));
            return data.join('&');
        }
        return null;
    };
    suraAjax.prototype.request = function(){
        var r = this.xhr, _s = this;

        r.onreadystatechange = function(){
            if (r.readyState == 4){
                if (r.status == 200) {
                    var data = r.responseText;
                    try{ var dobj = JSON.parse(data); }catch(e){ var dobj = {}; }

                    switch(dobj.cmd){
                        case 'login':
                            location.href = '/login';
                            return;
                            break;
                        case 'captcha':
                            anti_spam(function(code){
                                _s.data.captcha = code;
                                r.open('POST', _s.url, true);
                                r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                                r.send(_s.make_query());
                            }, _s.onFail);
                            return;
                            break;
                    }

                    if(_s.onDone) _s.onDone.apply(_s, [(_s.toJson ? dobj : data)]);
                }else{
                    r.status = parseInt(r.status);
                    if(r.status == 0 && window.addAllErr) addAllErr('<b>Error</b>: check connection to internet', 5000);
                    console.log('err', r.statusText, r.status);
                    if(_s.onFail) _s.onFail.apply(_s, [r.responseText,r.status]);
                }
                //delete _s;
            }
        };
        r.open(_s.method, _s.url, true);
        if(_s.method == 'POST'){
            r.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            r.send(_s.make_query());
        }else r.send(null);
        return _s;
    };
    suraAjax.prototype.done = function(fn){
        if(fn && Sura.isFunction(fn)) this.onDone = fn;
        else console.log('Передайте функцию для выполнения');
        return this;
    };
    suraAjax.prototype.fail = function(fn){
        if(fn && Sura.isFunction(fn)) this.onFail = fn;
        else console.log('Передайте функцию для выполнения');
        return this;
    };
    suraAjax.prototype.abort = function(fn){
        try{ this.xhr.abort(); }catch(e){ }
        return this;
    };
    Sura.ajax = function(url, opts){
        if(opts && opts.data && Sura.isFunction(opts.data)){
            opts.done = opts.data;
            delete opts.data;
        }
        return new suraAjax(url, opts);
    };
    Sura.post = function(url, query, callback, opts){
        var params = {data: query, method: 'POST', done: callback};
        if(opts && opts.obj) params.toJson = true;
        return Sura.ajax(url, params);
    };
    Sura.get = function(url, query, callback){
        return Sura.ajax(url, {data: query, method: 'GET', done: callback});
    };

    Sura.isFunction = function(obj) { return Object.prototype.toString.call(obj) === '[object Function]'; }

    Sura.extend = function(){
        var a = arguments, target = a[0] || {}, i = 1, l = a.length, deep = false, options;
        if (typeof target === 'boolean') {
            deep = target;
            target = a[1] || {};
            i = 2;
        }
        if (typeof target !== 'object' && !Sura.isFunction(target)) target = {};

        for (; i < l; ++i) {
            if ((options = a[i]) != null) {
                for (var name in options) {
                    var src = target[name], copy = options[name];

                    if (target === copy) continue;

                    if (deep && copy && typeof copy === 'object' && !copy.nodeType) target[name] = Sura.extend(deep, src || (copy.length != null ? [] : {}), copy);
                    else if (copy !== undefined) target[name] = copy;
                }
            }
        }
        return target;
    };

    Sura.isArray = function(obj) { return Object.prototype.toString.call(obj) === '[object Array]'; }
    Sura.isEmpty = function(o) { if(Object.prototype.toString.call(o) !== '[object Object]') {return false;} for(var i in o){ if(o.hasOwnProperty(i)){return false;} } return true; }

    Sura.clone = function(obj, req){
        var newObj = Sura.isArray(obj) ? [] : {};
        for(var i in obj) newObj[i] = (req && typeof(obj[i]) === 'object' && i !== 'prototype') ? clone(obj[i]) : obj[i];
        return newObj;
    };

    Sura.now = function(){ return +new Date; };

    var suraExpand = 'Sura' + (new Date().getTime()), suraUUID = 0, suraCache = {};
    Sura.data = function(elem, name, data){
        var id = elem[suraExpand], undefined;
        if(!id) id = elem[suraExpand] = ++suraUUID;
        if(data !== undefined) {
            if(!suraCache[id]) suraCache[id] = {};
            suraCache[id][name] = data;
        }
        return name ? suraCache[id] && suraCache[id][name] : id;
    };
    Sura.removeData = function(elem, name){
        if(!elem) return;
        var id = elem ? elem[suraExpand] : false;
        if(!id) return;

        if (name) {
            if (suraCache[id]) {
                delete suraCache[id][name];
                name = '';
                var count = 0;
                for (name in suraCache[id]) {
                    if (name !== '__elem') {
                        count++;
                        break;
                    }
                }
                if (!count) Sura.removeData(elem);
            }
        } else {
            Sura(elem).unbind().removeAttr(suraExpand);
            delete suraCache[id];
        }
    };

    Sura.addEvent = function(el, types, handler, opts){
        if(!el || el.nodeType == 3 || el.nodeType == 8) return;

        if(el.setInterval && el != window) el = window;

        if(!opts) opts = {};

        var events = Sura.data(el, 'events') || Sura.data(el, 'events', {}),
            handle = Sura.data(el, 'handle') || Sura.data(el, 'handle', function(){
                SuraEvents.apply(arguments.callee.el, arguments);
            });
        handle.el = el;

        Sura.each(types.split(/\s+/), function(index, type) {
            if(!events[type]){
                events[type] = [];
                if(el.addEventListener){
                    switch(type){
                        case 'mousedown':
                        case 'click': var capture = opts.no_capt ? false : true; break;
                        case 'paste': var capture = opts.capture ? true : false; break;
                        default: var capture = false;
                    }
                    el.addEventListener(type, handle, capture);
                }else el.attachEvent('on' + type, handle);
            }
            events[type].push(handler);
        });
    };

    function Sura_Indexer(s){
        s = s.trim();

        if(!s) return [];

        var create_match = s.match(/<(\s?)(div|li|span|img|ul|ol|a|link|script)(\s?)\/(\s?)>/);
        if(create_match){
            create_match = create_match[0].replace('<', '').replace('>', '').replace('/', '').trim();
            var el = document.createElement(create_match);
            return [el];
        }

        var is_visible = s.indexOf(':visible') != -1 ? true : false, is_filter = s.indexOf(':filter') != -1 ? true : false;
        if(is_visible){
            var exp = s.trim().split(' '), first = true, nodes = [], res = [];

            for(var i = 0; i < exp.length; i++){
                var e = exp[i];
                if(!e) continue;
                var visible = e.indexOf(':visible') != -1 ? true : false;
                if(visible) e = e.replace(':visible', '');
                if(first){
                    nodes = document.querySelectorAll(e);
                    first = false;
                }else{
                    var point = [];
                    for(var j = 0; j < nodes.length; j++){
                        var child = nodes[j].firstChild;
                        point.push(child);
                        while(child){
                            child = child.nextSibling;
                            if(child) point.push(child);
                        }
                    }
                    nodes = point;
                }
                res = [];

                var is_first = e.indexOf(':first') != -1 ? true : false, is_last = e.indexOf(':last') != -1 ? true : false;

                if(is_first) e = e.replace(':first', '');
                if(is_last) e = e.replace(':last', '');

                var first_symbol = e.substr(0, 1), is_class = first_symbol == '.' ? true : false, is_id = first_symbol == '#' ? true : false, is_tag = (!is_class && !is_id) ? true : false;
                if(is_tag){
                    var texp = e.split('.');
                    if(texp[1]) var is_podclass = true;
                    else{
                        texp = e.split('#');
                        if(texp[1]) var is_podid = true;
                    }
                    e = texp[0];
                    if(texp[1]) var podvalue = texp[1].toLowerCase().trim();
                }else var is_podclass = is_podid = podvalue = false;

                e = e.toLowerCase();

                if(is_id) e = e.replace('#', '');
                if(is_class) e = e.replace('.', '');


                for(var j = 0; j < nodes.length; j++){
                    var n = nodes[j];

                    if(is_class && (!n.className || n.className.toLowerCase().indexOf(e) == -1)) continue;
                    if(is_id && (!n.id || n.id.toLowerCase() != e)) continue;
                    if(is_tag && (!n.tagName || n.tagName.toLowerCase() != e)) continue;
                    if(is_podclass && (!n.className || !n.className.toLowerCase().indexOf(podvalue) == -1)) continue;
                    if(is_podid && (!n.id || !n.id.toLowerCase() != podvalue)) continue;

                    if(visible){
                        if(n.style && n.style['display'] != 'none') res.push(n);
                    }else res.push(n);
                }

                if(is_last) res = [res[res.length-1]];
                if(is_first) res = [res[0]]

                nodes = res;
            }
            return nodes;

        }else{
            var act = '';
            if(s.indexOf(':last') != -1) act = 'last';
            else if(s.indexOf(':first') != -1) act = 'first';

            s = s.replace(':first', '');
            s = s.replace(':prev', ':prev-child');
            s = s.replace(':next', ':next-child');
            s = s.replace(':last', '');

            var res = document.querySelectorAll(s);

            if(act == 'last') return res.length ? [res[res.length-1]] : [];
            else if(act == 'first') return res[0] ? [res[0]] : [];
            else return res;
        }
    }

    //Events
    function SuraEvents(e){
        e = e || window.event;
        if(!e) return;
        var el = e.target;
        var handlers = Sura.data(this, 'events');
        if (!handlers || typeof(e.type) != 'string' || !handlers[e.type] || !handlers[e.type].length) return;
        var eventHandlers = (handlers[e.type] || []).slice();
        for(var i in eventHandlers){
            if (e.type == 'mouseover' || e.type == 'mouseout') {
                var parent = e.relatedElement;
                while(parent && parent != this) {
                    console.log('wail');
                    try { parent = parent.parentNode; }
                    catch(e) { parent = this; }
                }
                if (parent == this) continue;
            }
            var ret = eventHandlers[i].apply(this, arguments);
            if (ret === false || ret === -1) e.preventDefault();
            if (ret === -1) return false;
        }
    }

    //Animate
    function animate(el, opts){
        if(!el) return;

        var start = new Date, delta = opts.delta || animateDelta.linear, tweens = Sura.data(el, 'tweens') || Sura.data(el, 'tweens', {});

        if(tweens[opts.prop]) clearInterval(tweens[opts.prop]);

        tweens[opts.prop] = setInterval(function(){
            var progress = (new Date - start) / opts.duration;
            if (progress > 1) progress = 1;

            opts.step(delta(progress));
            if (progress === 1) {
                clearInterval(tweens[opts.prop]);
                delete tweens[opts.prop];
                opts.complete && opts.complete();
            }
        }, opts.delay || 10);

        return tweens[opts.prop];
    }

    function animateProp(el, opts) {
        var start = opts.start, end = opts.end, prop = opts.prop;
        opts.step = function(delta) {
            var value = start + (end - (start))*delta;
            if(prop == 'scrollTop') el.scrollTop = Math.round(value);
            else{
                if(el.style) el.style[prop] = prop == 'opacity' ? value : Math.round(value) + 'px';
            }
        }
        return animate(el, opts);
    }

    var animateDelta = {
        linear: function(p){ return p; },
        quint: function(progress) { return Math.pow(progress, 5); }
    };

    function substitutionCSS(name){
        switch(name){
            case 'border-width': name = 'borderWidth'; break;
            case 'border-color': name = 'borderColor'; break;
            case 'border-left-width': name = 'borderLeftWidth'; break;
            case 'border-right-width': name = 'borderRightWidth'; break;
            case 'border-top-width': name = 'borderTopWidth'; break;
            case 'border-bottom-width': name = 'borderBottomWidth'; break;
        }
        return name;
    }

    function getAllEls(el){
        var els = el.querySelectorAll('*');
        return els;
    }

    window.Sura = Sura;
})(window, document);