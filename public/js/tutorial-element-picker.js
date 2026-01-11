// JS for element picking in tutorial picker mode
(function(){
    if (!window.pickerMode) return;
    
    // Highlight on hover
    document.body.addEventListener('mouseover', function(e) {
        if (!window.pickerMode) return;
        let el = e.target;
        if (el.closest('.tutorial-pick-ignore')) return;
        el.classList.add('tutorial-pick-highlight');
    }, true);
    document.body.addEventListener('mouseout', function(e) {
        if (!window.pickerMode) return;
        let el = e.target;
        el.classList.remove('tutorial-pick-highlight');
    }, true);

    // On click, send selector to opener or parent (iframe) and close
    document.body.addEventListener('click', function(e) {
        if (!window.pickerMode) return;
        let el = e.target;
        if (el.closest('.tutorial-pick-ignore')) return;
        e.preventDefault();
        e.stopPropagation();
        let selector = window.getTutorialSelector(el);
        // Support both window.opener (popup) and window.parent (iframe)
        if (window.parent && window.parent !== window) {
            window.parent.postMessage({type: 'tutorial-element-picked', selector}, '*');
        } else if (window.opener && selector) {
            window.opener.postMessage({type: 'tutorial-element-picked', selector}, '*');
        }
        setTimeout(() => window.close && window.close(), 200);
    }, true);

    // Utility: get unique selector
    window.getTutorialSelector = function(el) {
        if (!el) return '';
        // Prefer id if present and unique
        if (el.id && document.querySelectorAll('#' + CSS.escape(el.id)).length === 1) {
            return '#' + el.id;
        }
        // Prefer data attributes if present
        if (el.hasAttribute('data-tutorial-selector')) {
            return '[data-tutorial-selector="' + el.getAttribute('data-tutorial-selector') + '"]';
        }
        // Prefer unique class if present, but skip tutorial-pick-* classes
        if (el.classList.length > 0) {
            for (let cls of el.classList) {
                if (!cls || cls.startsWith('tutorial-pick-')) continue;
                if (document.getElementsByClassName(cls).length === 1) {
                    return '.' + cls;
                }
            }
        }
        // Otherwise, build a stable path (skip numeric div[x] unless needed)
        let path = [];
        let current = el;
        while (current && current.nodeType === 1 && current !== document.body) {
            let name = current.localName;
            if (!name) break;
            let selector = name;
            // Use class if unique in parent, skip tutorial-pick-* classes
            if (current.classList.length > 0) {
                let found = false;
                for (let cls of current.classList) {
                    if (!cls || cls.startsWith('tutorial-pick-')) continue;
                    if (current.parentNode && current.parentNode.getElementsByClassName(cls).length === 1) {
                        selector += '.' + cls;
                        found = true;
                        break;
                    }
                }
                // Only add all classes if none are tutorial-pick-*
                let filteredClasses = Array.from(current.classList).filter(c => c && !c.startsWith('tutorial-pick-'));
                if (!found && filteredClasses.length > 0) {
                    selector += '.' + filteredClasses.join('.');
                }
            }
            // Only add :nth-child if needed (multiple siblings of same tag/class)
            let parent = current.parentNode;
            if (parent) {
                let siblings = Array.from(parent.children).filter(child => child.localName === name);
                if (siblings.length > 1) {
                    selector += `:nth-child(${Array.from(parent.children).indexOf(current) + 1})`;
                }
            }
            path.unshift(selector);
            current = parent;
        }
        return path.length ? path.join(' > ') : '';
    };

    // Add style
    let style = document.createElement('style');
    style.innerHTML = `
        .tutorial-pick-highlight {
            outline: 2px solid #1bce8f !important;
            cursor: pointer !important;
            z-index: 9999 !important;
        }
        .tutorial-pick-ignore, .tutorial-pick-ignore * {
            pointer-events: none !important;
        }
    `;
    document.head.appendChild(style);

    // Mark sidebar/nav as ignore
    document.querySelectorAll('.sidebar-wrapper, .main-content > nav, .main-content > .navbar, .tutorial-pick-ignore').forEach(el => {
        el.classList.add('tutorial-pick-ignore');
    });

    // Expose flag
    window.pickerMode = true;
})();
