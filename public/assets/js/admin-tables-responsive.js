/**
 * GARDA 01 Portal — Global Responsive Admin Tables
 * Automatically enhances every table loaded by the admin layout.
 */
(function () {
    "use strict";

    const WRAPPER_CLASS = "g01-admin-table-scroll";
    const TABLE_CLASS = "g01-admin-table";
    const HINT_CLASS = "g01-admin-table-hint";

    function countColumns(table) {
        const row = table.querySelector("thead tr") || table.querySelector("tr");
        if (!row) return 1;

        return Array.from(row.children).reduce(function (total, cell) {
            const span = Number.parseInt(cell.getAttribute("colspan") || "1", 10);
            return total + (Number.isFinite(span) ? span : 1);
        }, 0);
    }

    function recommendedWidth(columnCount) {
        if (columnCount <= 4) return 640;
        if (columnCount === 5) return 760;
        if (columnCount === 6) return 880;
        if (columnCount === 7) return 1000;
        if (columnCount === 8) return 1120;
        if (columnCount === 9) return 1240;
        return 1360 + Math.max(0, columnCount - 10) * 110;
    }

    function findOrCreateWrapper(table) {
        const knownWrapper = table.closest(
            "." + WRAPPER_CLASS +
            ", .table-responsive" +
            ", .table-wrapper" +
            ", .table-scroll" +
            ", .data-table-wrapper" +
            ", .activity-table-scroll" +
            ", .activity-workflow-table-scroll"
        );

        if (knownWrapper) {
            knownWrapper.classList.add(WRAPPER_CLASS);
            return knownWrapper;
        }

        const wrapper = document.createElement("div");
        wrapper.className = WRAPPER_CLASS;
        table.parentNode.insertBefore(wrapper, table);
        wrapper.appendChild(table);
        return wrapper;
    }

    function createHint(wrapper) {
        let hint = wrapper.previousElementSibling;

        const reusableHint =
            hint &&
            (
                hint.classList.contains(HINT_CLASS) ||
                hint.classList.contains("activity-table-scroll-hint") ||
                hint.classList.contains("table-scroll-hint") ||
                hint.hasAttribute("data-table-scroll-hint")
            );

        if (reusableHint) {
            hint.classList.add(HINT_CLASS);
        }

        if (!reusableHint) {
            hint = document.createElement("div");
            hint.className = HINT_CLASS;
            hint.textContent =
                "Geser tabel ke samping untuk melihat seluruh kolom dan tindakan.";
            wrapper.parentNode.insertBefore(hint, wrapper);
        }

        return hint;
    }

    function updateOverflowState(wrapper, hint) {
        const hasOverflow = wrapper.scrollWidth > wrapper.clientWidth + 2;
        hint.classList.toggle("is-visible", hasOverflow);
    }

    function addPointerDrag(wrapper) {
        if (wrapper.dataset.dragScrollReady === "1") return;
        wrapper.dataset.dragScrollReady = "1";

        let pointerDown = false;
        let startX = 0;
        let startScrollLeft = 0;

        wrapper.addEventListener("pointerdown", function (event) {
            if (event.pointerType === "touch") return;

            const interactive = event.target.closest(
                "a, button, input, select, textarea, label, form"
            );

            if (interactive) return;

            pointerDown = true;
            startX = event.clientX;
            startScrollLeft = wrapper.scrollLeft;
            wrapper.setPointerCapture(event.pointerId);
            wrapper.style.cursor = "grabbing";
        });

        wrapper.addEventListener("pointermove", function (event) {
            if (!pointerDown) return;
            wrapper.scrollLeft = startScrollLeft - (event.clientX - startX);
        });

        function stopDrag(event) {
            if (!pointerDown) return;
            pointerDown = false;
            wrapper.style.cursor = "";

            if (
                event &&
                typeof event.pointerId !== "undefined" &&
                wrapper.hasPointerCapture(event.pointerId)
            ) {
                wrapper.releasePointerCapture(event.pointerId);
            }
        }

        wrapper.addEventListener("pointerup", stopDrag);
        wrapper.addEventListener("pointercancel", stopDrag);
        wrapper.addEventListener("lostpointercapture", stopDrag);
    }

    function enhanceTable(table, index) {
        if (
            table.dataset.g01ResponsiveReady === "1" ||
            table.hasAttribute("data-no-responsive-table")
        ) {
            return;
        }

        table.dataset.g01ResponsiveReady = "1";
        table.classList.add(TABLE_CLASS);

        const columnCount = countColumns(table);
        const wrapper = findOrCreateWrapper(table);
        const hint = createHint(wrapper);

        wrapper.setAttribute("tabindex", "0");
        wrapper.setAttribute("role", "region");

        if (!wrapper.getAttribute("aria-label")) {
            const caption = table.querySelector("caption");
            wrapper.setAttribute(
                "aria-label",
                caption && caption.textContent.trim()
                    ? caption.textContent.trim()
                    : "Tabel data " + (index + 1)
            );
        }

        wrapper.style.setProperty(
            "--g01-table-min-width",
            recommendedWidth(columnCount) + "px"
        );

        addPointerDrag(wrapper);

        const resizeObserver = new ResizeObserver(function () {
            updateOverflowState(wrapper, hint);
        });

        resizeObserver.observe(wrapper);
        resizeObserver.observe(table);

        requestAnimationFrame(function () {
            updateOverflowState(wrapper, hint);
        });
    }

    function enhanceAllTables(root) {
        const scope = root || document;
        scope.querySelectorAll("table").forEach(function (table, index) {
            enhanceTable(table, index);
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        enhanceAllTables(document);

        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    if (!(node instanceof Element)) return;

                    if (node.matches("table")) {
                        enhanceTable(node, 0);
                    } else {
                        enhanceAllTables(node);
                    }
                });
            });
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true
        });
    });
})();
