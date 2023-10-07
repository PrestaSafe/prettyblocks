export class toolbar {
  constructor(targets, document = document, window = window) {
    this.events = {};
    this.arr = [];
    this.tEdited;
    this.pApply;
    this.document = document;
    this.window = window;

    this.targets = targets;
    this.targets.forEach((element) => {
      const id = Math.random()
        .toString(36)
        .substr(2, 9);
      element.setAttribute("data-id-title", id);

      let dataAttributesString = element.dataset.attributes; 
      const attrObject = JSON.parse(dataAttributesString);

      this.arr.push({
        id: id,
        html: element,
        value: element.innerHTML,
        tag: element.tagName.toLowerCase(),
        classes: element.classList,
        focus: this.getAttributeValue(attrObject, "focus"),
        inside: false, // Ici, je suppose que "inside" n'est pas un attribut de données, donc je le laisse inchangé
        bold: this.getAttributeValue(attrObject, "bold"),
        italic: this.getAttributeValue(attrObject, "italic"),
        underline: this.getAttributeValue(attrObject, "underline"),
        size: this.getAttributeValue(attrObject, "size") ? this.getAttributeValue(attrObject, "size") : 32,
      });
    });

    for (const t of this.arr) {
      const id = t.id;
      const e = this.document.querySelector('[data-id-title="' + id + '"]');
      e.setAttribute("contenteditable", "true");
      t.size = e.style.fontSize;
    }

    this.toolbar = this.document.createElement("div");
    this.toolbar.id = "toolbar";
    this.document.getElementsByTagName("body")[0].appendChild(this.toolbar);

    this.select = this.document.createElement("select");
    this.select.id = "select";
    this.select.innerHTML = `
      <option value="h1">H1</option>
      <option value="h2">H2</option>
      <option value="h3">H3</option>
      <option value="h4">H4</option>
      <option value="h5">H5</option>
      <option value="h6">H6</option>
      <option value="p">p</option>
      <option value="span">span</option>
      `;

    this.select.selectedIndex = 1;

    this.toolbar.appendChild(this.select);

    this.size = this.document.createElement("input");
    this.size.id = "size";
    this.size.type = "number";
    this.size.value = "32";
    this.size.min = "1";
    this.size.max = "100";
    this.size.step = "1";
    this.toolbar.appendChild(this.size);

    this.sep = this.document.createElement("div");
    this.sep.classList = "sep";
    this.toolbar.appendChild(this.sep);

    this.B = this.document.createElement("button");
    this.Bo = false;
    this.B.id = "Bold";
    this.B.innerHTML = "B";
    this.toolbar.appendChild(this.B);
    this.I = this.document.createElement("button");
    this.Io = false;
    this.I.id = "Italics";
    this.I.innerHTML = "I";
    this.toolbar.appendChild(this.I);
    this.U = this.document.createElement("button");
    this.Uo = false;
    this.U.id = "Underline";
    this.U.innerHTML = "U";
    this.toolbar.appendChild(this.U);
    this.init();

    this.setVisibility();
  }

  init() {
    for (const t of this.arr) {
      let e = this.document.querySelector('[data-id-title="' + t.id + '"]');

      for (const i of this.targets) {
        this.setBinging(i, t, e);
      }

      this.select.addEventListener("change", () => {
        const z = {
          id: t.id,
          focus: t.focus,
          tag: this.select.value,
          classes: Array.from(e.classList), 
          inside: t.inside,
          bold: t.bold,
          italic: t.italic,
          underline: t.underline,
          size: t.size,
        };

        const lastT = structuredClone(z);

        if (this.tEdited == t.id) {
          let text = e.innerHTML;
          let tag = this.select.value;
          let newElement = this.document.createElement(tag);
          newElement.innerHTML = text;
          newElement.classList = e.classList;
          e.replaceWith(newElement);
          let id_prettyblocks = e.getAttribute("data-block-id_prettyblock");
          newElement.setAttribute("data-block-id_prettyblock", id_prettyblocks);

          let data_field = e.getAttribute("data-field");
          newElement.setAttribute("data-field", data_field);

          e = newElement;
          e.setAttribute("data-id-title", t.id);
          e.setAttribute("contenteditable", "true");

          if (t.bold == true) {
            e.style.fontWeight = "bold";
          }
          if (t.italic == true) {
            e.style.fontStyle = "italic";
          }
          if (t.underline == true) {
            e.style.textDecoration = "underline";
          }
          e.style.fontSize = t.size + "px";
          this.setVisibility();
          t.html = newElement;
          this.change(lastT, t);
          this.setBinging(newElement, t, e);
        }
      });

      this.size.addEventListener("change", () => {
        if (this.tEdited == t.id) {
          const z = {
            id: t.id,
            focus: t.focus,
            tag: this.select.value,
            classes: Array.from(e.classList), 
            inside: t.inside,
            bold: t.bold,
            italic: t.italic,
            underline: t.underline,
            size: t.size,
          };
          const lastT = structuredClone(z);

          e.style.fontSize = this.size.value + "px";
          t.size = this.size.value;

          this.change(lastT, t);
        }
      });

      this.B.addEventListener("click", () => {
        if (this.tEdited == t.id) {
          const z = {
            id: t.id,
            focus: t.focus,
            tag: this.select.value,
            classes: Array.from(e.classList), 
            inside: t.inside,
            bold: t.bold,
            italic: t.italic,
            underline: t.underline,
            size: t.size,
          };
          const lastT = structuredClone(z);

          if (t.bold == false) {
            t.bold = true;
            this.B.style.color = "#6ae26a";
            e.style.fontWeight = "bold";

            this.change(lastT, t);
          } else {
            t.bold = false;
            this.B.style.color = "white";
            e.style.fontWeight = "normal";

            this.change(lastT, t);
          }
        }
      });
      this.I.addEventListener("click", () => {
        if (this.tEdited == t.id) {
          const z = {
            id: t.id,
            focus: t.focus,
            tag: this.select.value,
            classes: Array.from(e.classList), 
            inside: t.inside,
            bold: t.bold,
            italic: t.italic,
            underline: t.underline,
            size: t.size,
          };
          const lastT = structuredClone(z);

          if (t.italic == false) {
            t.italic = true;
            this.I.style.color = "#6ae26a";
            e.style.fontStyle = "italic";

            this.change(lastT, t);
          } else {
            t.italic = false;
            this.I.style.color = "white";
            e.style.fontStyle = "normal";

            this.change(lastT, t);
          }
        }
      });
      this.U.addEventListener("click", () => {
        if (this.tEdited == t.id) {
          let underline = !t.underline;
          // console.log('underline', underline)
          this.setAttributeValue(e,"underline", underline);
          t.underline = underline;
          const z = {
            id: t.id,
            focus: t.focus,
            inside: t.inside,
            tag: this.select.value,
            classes: Array.from(e.classList), 
            bold: t.bold,
            italic: t.italic,
            underline: t.underline,
            size: t.size,
          };

          const lastT = structuredClone(z);


          if (t.underline) {
            this.U.style.color = "#6ae26a";
            e.style.textDecoration = "underline";
          } else {
            this.U.style.color = "white";
            e.style.textDecoration = "none";
          }

          this.change(lastT, t);
        }
      });
    }
  }

  setBinging(i, t, e) {
    if (i.getAttribute("data-id-title") == t.id) {
      i.addEventListener("keydown", (k) => {
        if (
          (k.ctrlKey && k.key == "s") ||
          (k.ctrlKey && k.key == "S") ||
          (k.metaKey && k.key == "s") ||
          (k.metaKey && k.key == "S")
        ) {
          k.preventDefault();

          t.value = e.innerHTML;
          this.change(this.pApply, t);
          i.blur();
        }
        if (k.shiftKey && k.key == "Enter") {
          k.preventDefault();
          this.document.execCommand("insertHTML", false, "<br><br>");
        } else if (k.key == "Enter") {
          k.preventDefault();
          t.value = e.innerHTML;
          this.change(this.pApply, t);
          i.blur();
        }
      });
    }
  }

  getAttributeValue(attrObject, attributeName) {
    return attrObject.hasOwnProperty(attributeName) ? attrObject[attributeName] : false;
  }
  
  setAttributeValue(element, attributeName, newValue) {
    let attrString = element.getAttribute('data-attributes');
    let attrObject = null;
    attrObject = JSON.parse(attrString);  
    if (attrObject && attrObject.hasOwnProperty(attributeName)) {
        attrObject[attributeName] = newValue;
    } else {
        // console.log("L'attribut " + attributeName + " n'existe pas dans l'objet.");
    }
    // console.log('OK', attrObject)
    element.setAttribute('data-attributes', JSON.stringify(attrObject || {}));
    
  }


  setVisibility() {
    for (const t of this.arr) {
      const e = this.document.querySelector('[data-id-title="' + t.id + '"]');
      let inside = false;
      let focus = false;
      e.addEventListener("mousedown", (e) => {
        focus = true;
        this.toolbar.style.display = "flex";
        const id = e.target.getAttribute("data-id-title");
        const res = this.arr.filter((item) => item.id == id)[0];
        this.refreshToolbar(res);
        const z = {
          id: t.id,
          focus: t.focus,
          inside: t.inside,
          bold: t.bold,
          tag: this.select.value,
          italic: t.italic,
          underline: t.underline,
          size: t.size,
          value: e.target.innerHTML,
        };

        this.pApply = structuredClone(z);
      });

      e.addEventListener("mouseleave", () => {
        inside = false;
        if (!focus) {
          setTimeout(() => {
            if (!inside && !focus) {
              this.toolbar.style.display = "none";
            }
          }, 1000);
        }
      });

      e.addEventListener("focus", (e) => {
        focus = true;
        this.toolbar.style.display = "flex";

        const id = e.target.getAttribute("data-id-title");
        const res = this.arr.filter((item) => item.id == id)[0];
        this.refreshToolbar(res);
      });

      e.addEventListener("blur", () => {
        focus = false;
        if (!inside) {
          setTimeout(() => {
            if (!inside && !focus) {
              this.toolbar.style.display = "none";
            }
          }, 1000);
        }
      });

      this.toolbar.addEventListener("mouseenter", (e) => {
        inside = true;
        this.toolbar.style.display = "flex";
      });

      this.toolbar.addEventListener("mouseleave", (event) => {
        const e = event.toElement || event.relatedTarget;
        inside = false;
        setTimeout(() => {
          if (
            e &&
            (e.parentNode === this ||
              e === this ||
              e.parentNode === this.toolbar)
          ) {
            return;
          } else if (!focus) {
            this.toolbar.style.display = "none";
          }
        }, 1000);
      });
    }
  }

  refreshToolbar(obj) {
    // console.log('refreshToolbar', obj)
    const id = obj.id;
    const e = this.document.querySelector('[data-id-title="' + id + '"]');
    const tag = e.tagName.toLowerCase()

    const top = this.findTop(e);
    const left = this.findLeft(e);
    const fz = Math.round(
      this.window
        .getComputedStyle(e, null)
        .getPropertyValue("font-size")
        .split("px")[0]
    );
    const p = e.getBoundingClientRect();
    const tp = this.toolbar.getBoundingClientRect();

    this.toolbar.style.top = top - tp.height + 55 + "px";
    this.toolbar.style.left = left + p.width / 2 - tp.width + "px";

    this.tEdited = obj.id;
    this.size.value = fz;
    obj.size = fz;

    if (tag == "h1") this.select.selectedIndex = 0;
    if (tag == "h2") this.select.selectedIndex = 1;
    if (tag == "h3") this.select.selectedIndex = 2;
    if (tag == "h4") this.select.selectedIndex = 3;
    if (tag == "h5") this.select.selectedIndex = 4;
    if (tag == "h6") this.select.selectedIndex = 5;
    if (tag == "p") this.select.selectedIndex = 6;
    if (tag == "span") this.select.selectedIndex = 7;

    if (obj.bold) {
      this.B.style.color = "#6ae26a";
    } else {
      this.B.style.color = "white";
    }
    if (obj.italic) {
      this.I.style.color = "#6ae26a";
    } else {
      this.I.style.color = "white";
    }
    if (obj.underline) {
      this.U.style.color = "#6ae26a";
    } else {
      this.U.style.color = "white";
    }
  }

  // event with callback
  on(event, callback) {
    if (!this.events[event]) {
      this.events[event] = [];
    }
    this.events[event].push(callback);
  }

  trigger(event, ...args) {
    const callbacks = this.events[event];
    if (callbacks) {
      callbacks.forEach((callback) => {
        callback(...args);
      });
    }
  }

  change(lastValue, newValue) {
    lastValue.html = newValue.html;
    lastValue.value = newValue.value;  
    lastValue.classes = newValue.classes;  
    lastValue.bold = newValue.bold;  
    lastValue.italic = newValue.italic;
    lastValue.underline = newValue.underline;
    
    lastValue.size = newValue.size;
    this.trigger("change", lastValue, newValue);
  }

  apply(lastValue, newValue) {
    if (!newValue.inside && !newValue.focus) {
      lastValue.html = newValue?.html;
      newValue.value = newValue.html.innerHTML;
      this.trigger("apply", lastValue, newValue);
    }
  }

  /**
   * https://stackoverflow.com/questions/442404/retrieve-the-position-x-y-of-an-html-element/44113758#44113758
   * @param {*} element
   * @returns  {number}
   *
   */
  findTop(element) {
    var rec = element.getBoundingClientRect();
    return rec.top + this.window.scrollY;
  } //call it like findTop('#header');

  findLeft(element) {
    var rec = element.getBoundingClientRect();
    return rec.left + this.window.scrollX;
  } //call it like findLeft('#header');
}
