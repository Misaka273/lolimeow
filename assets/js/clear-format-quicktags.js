document.addEventListener('DOMContentLoaded', function() {
  if (typeof QTags !== 'undefined') {
    function cleanHTML(html) {
      var root = document.createElement('div');
      root.innerHTML = html;
      function walk(node) {
        if (node.nodeType === 1) {
          for (var i = node.attributes.length - 1; i >= 0; i--) {
            var name = node.attributes[i].name.toLowerCase();
            var tag = node.tagName.toLowerCase();
            if (tag === 'a' && name === 'href') continue;
            if (tag === 'img' && (name === 'src' || name === 'alt' || name === 'title')) continue;
            if (name === 'style' || name === 'class' || name === 'id' || name.indexOf('on') === 0 || name.indexOf('data-') === 0) {
              node.removeAttribute(node.attributes[i].name);
            }
          }
          var t = node.tagName.toLowerCase();
          if (t === 'br') {
            node.parentNode.replaceChild(document.createTextNode('\n'), node);
            return;
          }
          if (t === 'span' || t === 'font') {
            var p1 = node.parentNode;
            while (node.firstChild) p1.insertBefore(node.firstChild, node);
            p1.removeChild(node);
            return;
          }
          if (t === 'div' || t === 'p' || t === 'section' || t === 'article') {
            var p2 = node.parentNode;
            var frag = document.createDocumentFragment();
            while (node.firstChild) frag.appendChild(node.firstChild);
            p2.insertBefore(frag, node);
            p2.insertBefore(document.createTextNode('\n'), node);
            p2.removeChild(node);
            return;
          }
        }
        var c = node.firstChild;
        while (c) { var n = c.nextSibling; walk(c); c = n; }
      }
      walk(root);
      var out = root.innerHTML;
      out = out.replace(/\r\n|\r/g, '\n');
      out = out.replace(/\n{2,}/g, '\n');
      return out;
    }
    QTags.addButton('boxmoe_clearformat', '清除格式', function() {
      var el = document.getElementById('content');
      if (!el) return;
      var start = el.selectionStart;
      var end = el.selectionEnd;
      if (start === end) return;
      var selected = el.value.substring(start, end);
      var cleaned = cleanHTML(selected);
      cleaned = cleaned.replace(/\r\n|\r/g, '\n').replace(/\n{2,}/g, '\n');
      el.value = el.value.substring(0, start) + cleaned + el.value.substring(end);
      el.selectionStart = start;
      el.selectionEnd = start + cleaned.length;
    });
  }
});
