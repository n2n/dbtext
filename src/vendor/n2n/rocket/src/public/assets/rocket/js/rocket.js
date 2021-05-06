var Rocket;
(function (Rocket) {
    let container;
    let blocker;
    let initializer;
    let $ = jQuery;
    jQuery(document).ready(function ($) {
        let jqContainer = $("#rocket-content-container");
        container = new Rocket.Cmd.Container(jqContainer);
        let userStore = Rocket.Impl.UserStore
            .read(jqContainer.find("#rocket-global-nav").find("h2").data("rocketUserId"));
        blocker = new Rocket.Cmd.Blocker(container);
        blocker.init($("body"));
        initializer = new Rocket.Display.Initializer(container, jqContainer.data("error-tab-title"), jqContainer.data("display-error-label"));
        let clipboard = new Rocket.Impl.Relation.Clipboard();
        Jhtml.ready(() => {
            initializer.scan();
        });
        (function () {
            Jhtml.ready(() => {
                $(".rocket-impl-overview").each(function () {
                    Rocket.Impl.Overview.OverviewPage.from($(this));
                });
            });
            Jhtml.ready(() => {
                $(".rocket-impl-overview").each(function () {
                    Rocket.Impl.Overview.OverviewPage.from($(this));
                });
            });
        })();
        (function () {
            $("form.rocket-form").each(function () {
                Rocket.Impl.Form.from($(this));
            });
            Jhtml.ready(() => {
                $("form.rocket-form").each(function () {
                    Rocket.Impl.Form.from($(this));
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-many").each(function () {
                Rocket.Impl.Relation.ToMany.from($(this), clipboard);
            });
            Jhtml.ready(() => {
                $(".rocket-impl-to-many").each(function () {
                    Rocket.Impl.Relation.ToMany.from($(this), clipboard);
                });
            });
        })();
        (function () {
            $(".rocket-impl-to-one").each(function () {
                let toOne = Rocket.Impl.Relation.ToOne.from($(this), clipboard);
            });
            Jhtml.ready(() => {
                $(".rocket-impl-to-one").each(function () {
                    let toOne = Rocket.Impl.Relation.ToOne.from($(this), clipboard);
                });
            });
        })();
        (function () {
            let t = new Rocket.Impl.Translation.Translator(container, userStore);
            Jhtml.ready(() => {
                t.scan();
            });
        })();
        (function () {
            Jhtml.ready((elements) => {
                $(elements).find("a.rocket-jhtml").each(function () {
                    new Rocket.Display.Command(Jhtml.Ui.Link.from(this)).observe();
                });
            });
        })();
        (function () {
            Jhtml.ready((elements) => {
                $(elements).find(".rocket-image-resizer-container").each(function () {
                    new Rocket.Impl.File.RocketResizer($(this));
                });
            });
        })();
        (function () {
            let moveState = new Rocket.Impl.Order.MoveState();
            Jhtml.ready((elements) => {
                $(elements).find(".rocket-impl-insert-before").each(function () {
                    let elemJq = $(this);
                    new Rocket.Impl.Order.Control(elemJq, Rocket.Impl.Order.InsertMode.BEFORE, moveState, elemJq.siblings(".rocket-static"));
                });
                $(elements).find(".rocket-impl-insert-after").each(function () {
                    let elemJq = $(this);
                    new Rocket.Impl.Order.Control(elemJq, Rocket.Impl.Order.InsertMode.AFTER, moveState, elemJq.siblings(".rocket-static"));
                });
                $(elements).find(".rocket-impl-insert-as-child").each(function () {
                    let elemJq = $(this);
                    new Rocket.Impl.Order.Control(elemJq, Rocket.Impl.Order.InsertMode.CHILD, moveState, elemJq.siblings(".rocket-static"));
                });
            });
        })();
        (function () {
            var nav = new Rocket.Display.Nav();
            var navGroups = [];
            Jhtml.ready((elements) => {
                let elementsJq = $(elements);
                let rgn = elementsJq.find("#rocket-global-nav");
                if (rgn.length > 0) {
                    nav.elemJq = rgn;
                    let navGroupJq = rgn.find(".rocket-nav-group");
                    navGroupJq.each((key, navGroupNode) => {
                        navGroups.push(Rocket.Display.NavGroup.build($(navGroupNode), userStore));
                    });
                    rgn.on("scroll", () => {
                        userStore.navState.scrollPos = rgn.scrollTop();
                        userStore.save();
                    });
                    var observer = new MutationObserver((mutations) => {
                        nav.scrollToPos(userStore.navState.scrollPos);
                        mutations.forEach((mutation) => {
                            navGroups.forEach((navGroup) => {
                                if ($(Array.from(mutation.removedNodes)[0]).get(0) === navGroup.elemJq.get(0)) {
                                    userStore.navState.offChanged(navGroup);
                                }
                            });
                            navGroups.forEach((navGroup) => {
                                if ($(Array.from(mutation.addedNodes)[0]).get(0) === navGroup.elemJq.get(0)) {
                                    if (userStore.navState.isGroupOpen(navGroup.id)) {
                                        navGroup.open(0);
                                    }
                                    else {
                                        navGroup.close(0);
                                    }
                                    nav.scrollToPos(userStore.navState.scrollPos);
                                }
                            });
                        });
                    });
                    observer.observe(rgn.get(0), { childList: true });
                }
                elementsJq.each((key, node) => {
                    let nodeJq = $(node);
                    if (nodeJq.hasClass("rocket-nav-group") && nodeJq.parent().get(0) === nav.elemJq.get(0)) {
                        navGroups.push(Rocket.Display.NavGroup.build(nodeJq, userStore));
                    }
                });
                nav.scrollToPos(userStore.navState.scrollPos);
            });
        })();
        (function () {
            Jhtml.ready((elements) => {
                var elementsJq = $(elements);
                elementsJq.find(".dropdown").each((i, elem) => {
                    var elemJq = $(elem);
                    Rocket.Display.Toggler.simple(elemJq.find(".dropdown-toggle"), elemJq.find(".dropdown-menu"));
                });
            });
        })();
        (function () {
            let url = $("[data-jhtml-container][data-rocket-url]").data("rocket-url");
            if (!url)
                return;
            setInterval(() => {
                $.get(url);
            }, 300000);
        })();
        (function () {
            Jhtml.ready((elements) => {
                var elementsJq = $(elements);
                elementsJq.find(".rocket-privilege-form").each(function () {
                    (new Rocket.Core.PrivilegeForm($(this))).setup();
                });
                elementsJq.find(".rocket-command-privileges").each(function () {
                    (new Rocket.Core.CommandPrivilegeList($(this))).listen();
                });
            });
        })();
        (function () {
            Jhtml.ready((elements) => {
                var elementsJq = $(elements);
                elementsJq.find(".rocket-mail").each(function () {
                    let mle = new Rocket.Core.MailLogEntry($(this));
                    mle.listen();
                });
                elementsJq.find(".rocket-mail-paging").each(function () {
                    (new Rocket.Core.MailPaging($(this))).listen();
                });
            });
        })();
    });
    function scan(context = null) {
        initializer.scan();
    }
    Rocket.scan = scan;
    function getContainer() {
        return container;
    }
    Rocket.getContainer = getContainer;
    function layerOf(elem) {
        return Rocket.Cmd.Layer.of($(elem));
    }
    Rocket.layerOf = layerOf;
    function contextOf(elem) {
        return Rocket.Cmd.Zone.of($(elem));
    }
    Rocket.contextOf = contextOf;
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Blocker {
            constructor(container) {
                this.container = container;
                this.jqBlocker = null;
                for (let layer of container.layers) {
                    this.observeLayer(layer);
                }
                var that = this;
                container.layerOn(Cmd.Container.LayerEventType.ADDED, function (layer) {
                    that.observeLayer(layer);
                    that.check();
                });
            }
            observeLayer(layer) {
                for (let context of layer.zones) {
                    this.observePage(context);
                }
                layer.onNewZone((context) => {
                    this.observePage(context);
                    this.check();
                });
            }
            observePage(context) {
                var checkCallback = () => {
                    this.check();
                };
                context.on(Cmd.Zone.EventType.SHOW, checkCallback);
                context.on(Cmd.Zone.EventType.HIDE, checkCallback);
                context.on(Cmd.Zone.EventType.CLOSE, checkCallback);
                context.on(Cmd.Zone.EventType.CONTENT_CHANGED, checkCallback);
                context.on(Cmd.Zone.EventType.BLOCKED_CHANGED, checkCallback);
            }
            init(jqContainer) {
                if (this.jqContainer) {
                    throw new Error("Blocker already initialized.");
                }
                this.jqContainer = jqContainer;
                this.check();
            }
            check() {
                if (!this.jqContainer || !this.container.currentLayer.currentZone)
                    return;
                if (!this.container.currentLayer.currentZone.locked) {
                    if (!this.jqBlocker)
                        return;
                    this.jqBlocker.remove();
                    this.jqBlocker = null;
                    return;
                }
                if (this.jqBlocker)
                    return;
                this.jqBlocker =
                    $("<div />", {
                        "class": "rocket-zone-block",
                        "css": {
                            "position": "fixed",
                            "top": 0,
                            "left": 0,
                            "right": 0,
                            "bottom": 0
                        }
                    })
                        .append($("<div />", { "class": "rocket-loader" }))
                        .appendTo(this.jqContainer);
            }
        }
        Cmd.Blocker = Blocker;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Layer {
            constructor(jqLayer, _level, _container, _monitor) {
                this.jqLayer = jqLayer;
                this._level = _level;
                this._container = _container;
                this._monitor = _monitor;
                this._zones = new Array();
                this.callbackRegistery = new Rocket.Util.CallbackRegistry();
                this._visible = true;
                this.scrollPos = 0;
                this.onNewZoneCallbacks = new Array();
                this.onNewHistoryEntryCallbacks = new Array();
                var zoneJq = jqLayer.children(".rocket-zone:first");
                if (zoneJq.length > 0) {
                    let url = Jhtml.Url.create(window.location.href);
                    var zone = new Cmd.Zone(zoneJq, url, this);
                    let page = this.monitor.history.currentPage;
                    page.promise = this.createPromise(zone);
                    zone.page = page;
                    this.addZone(zone);
                }
                this.monitor.history.onChanged(() => this.historyChanged());
                this.monitor.registerCompHandler("rocket-page", this);
                this.historyChanged();
            }
            get jQuery() {
                return this.jqLayer;
            }
            get monitor() {
                return this._monitor;
            }
            containsUrl(url) {
                for (var i in this._zones) {
                    if (this._zones[i].containsUrl(url))
                        return true;
                }
                return false;
            }
            getZoneByUrl(urlExpr) {
                let url = Jhtml.Url.create(urlExpr);
                for (let i in this._zones) {
                    if (this._zones[i].containsUrl(url)) {
                        return this._zones[i];
                    }
                }
                return null;
            }
            historyChanged() {
                let currentEntry = this.monitor.history.currentEntry;
                if (!currentEntry)
                    return;
                let page = currentEntry.page;
                let zone = this.getZoneByUrl(page.url);
                if (!zone) {
                    zone = this.createZone(page.url);
                    zone.clear(true);
                }
                if (!zone.page) {
                    zone.page = page;
                }
                this.switchToZone(zone);
            }
            createZone(urlExpr) {
                let url = Jhtml.Url.create(urlExpr);
                if (this.containsUrl(url)) {
                    throw new Error("Page with url already available: " + url);
                }
                var jqZone = $("<div />");
                this.jqLayer.append(jqZone);
                var zone = new Cmd.Zone(jqZone, url, this);
                this.addZone(zone);
                return zone;
            }
            get currentZone() {
                if (this.empty || !this._monitor.history.currentEntry) {
                    return null;
                }
                var url = this._monitor.history.currentPage.url;
                for (var i in this._zones) {
                    if (this._zones[i].containsUrl(url)) {
                        return this._zones[i];
                    }
                }
                return null;
            }
            get container() {
                return this._container;
            }
            get visible() {
                return this._visible;
            }
            trigger(eventType) {
                var layer = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(layer);
                });
            }
            on(eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            }
            off(eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            }
            show() {
                this._visible = true;
                this.jqLayer.show();
                this.trigger(Layer.EventType.SHOWED);
            }
            hide() {
                this._visible = false;
                this.jqLayer.hide();
                this.trigger(Layer.EventType.HIDDEN);
            }
            get level() {
                return this._level;
            }
            get empty() {
                return this._zones.length == 0;
            }
            get zones() {
                return this._zones.slice();
            }
            addZone(zone) {
                this._zones.push(zone);
                var that = this;
                zone.on(Cmd.Zone.EventType.CLOSE, function (zone) {
                    for (var i in that._zones) {
                        if (that._zones[i] !== zone)
                            continue;
                        that._zones.splice(parseInt(i), 1);
                        break;
                    }
                });
                for (var i in this.onNewZoneCallbacks) {
                    this.onNewZoneCallbacks[i](zone);
                }
            }
            set active(active) {
                if (active == this.active)
                    return;
                if (this.monitor) {
                    this.monitor.active = active;
                }
                if (active) {
                    this.jqLayer.addClass("rocket-active");
                    $(window).scrollTop(this.scrollPos);
                    return;
                }
                this.scrollPos = $(window).scrollTop();
                this.jqLayer.removeClass("rocket-active");
            }
            get active() {
                return this.jqLayer.hasClass("rocket-active");
            }
            onNewZone(onNewPageCallback) {
                this.onNewZoneCallbacks.push(onNewPageCallback);
            }
            clear() {
                for (var i in this._zones) {
                    this._zones[i].close();
                }
            }
            close() {
                this.trigger(Layer.EventType.CLOSE);
                let zone = null;
                while (zone = this._zones.pop()) {
                    zone.close();
                }
                this.jqLayer.remove();
            }
            switchToZone(zone) {
                for (var i in this._zones) {
                    if (this._zones[i] === zone) {
                        zone.show();
                    }
                    else {
                        this._zones[i].hide();
                    }
                }
            }
            attachComp(comp) {
                if (comp.isAttached)
                    return true;
                let url = this.monitor.history.currentPage.url;
                let zone = this.getZoneByUrl(url);
                if (!zone) {
                    throw new Error("Zone for url " + url + " does not extist");
                }
                zone.applyComp(comp);
                return true;
            }
            detachComp(comp) {
                return true;
            }
            pushHistoryEntry(urlExpr) {
                let url = Jhtml.Url.create(urlExpr);
                let history = this.monitor.history;
                let page = history.getPageByUrl(url);
                if (page) {
                    history.push(page);
                    return;
                }
                let zone = this.getZoneByUrl(url);
                if (zone) {
                    page = new Jhtml.Page(url, this.createPromise(zone));
                    history.push(page);
                    return;
                }
                history.push(new Jhtml.Page(url, null));
            }
            createPromise(zone) {
                return new Promise((resolve) => {
                    resolve({
                        getAdditionalData() {
                            return null;
                        },
                        exec() {
                            zone.layer.switchToZone(zone);
                        }
                    });
                });
            }
            static create(jqLayer, _level, _container, history) {
                if (Layer.test(jqLayer)) {
                    throw new Error("Layer already bound to this element.");
                }
                jqLayer.addClass("rocket-layer");
                jqLayer.data("rocketLayer", this);
            }
            static test(jqLayer) {
                var layer = jqLayer.data("rocketLayer");
                if (layer instanceof Layer) {
                    return layer;
                }
                return null;
            }
            static of(jqElem) {
                if (!jqElem.hasClass(".rocket-layer")) {
                    jqElem = jqElem.closest(".rocket-layer");
                }
                var layer = Layer.test(jqElem);
                if (layer === undefined) {
                    return null;
                }
                return layer;
            }
        }
        Cmd.Layer = Layer;
        (function (Layer) {
            let EventType;
            (function (EventType) {
                EventType[EventType["SHOWED"] = 0] = "SHOWED";
                EventType[EventType["HIDDEN"] = 1] = "HIDDEN";
                EventType[EventType["CLOSE"] = 2] = "CLOSE";
            })(EventType = Layer.EventType || (Layer.EventType = {}));
        })(Layer = Cmd.Layer || (Cmd.Layer = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Container {
            constructor(jqContainer) {
                this.layerCallbackRegistery = new Rocket.Util.CallbackRegistry();
                this.jqContainer = jqContainer;
                this._layers = new Array();
                var layer = new Cmd.Layer(this.jqContainer.find(".rocket-main-layer"), this._layers.length, this, Jhtml.getOrCreateMonitor());
                this.registerLayer(layer);
                jQuery(document).keyup((e) => {
                    if (e.keyCode == 27 && !$(e.target).is("input, textarea, button")) {
                        this.closePopup();
                    }
                });
            }
            closePopup() {
                if (this.currentLayer.level == 0)
                    return;
                this.currentLayer.close();
            }
            get layers() {
                return this._layers.slice();
            }
            get mainLayer() {
                if (this._layers.length > 0) {
                    return this._layers[0];
                }
                throw new Error("Container empty.");
            }
            markCurrent() {
                for (let layer of this._layers) {
                    layer.active = false;
                }
                this.currentLayer.active = true;
            }
            get currentLayer() {
                if (this._layers.length == 0) {
                    throw new Error("Container empty.");
                }
                var layer = null;
                for (let i in this._layers) {
                    if (this._layers[i].visible) {
                        layer = this._layers[i];
                    }
                }
                if (layer !== null)
                    return layer;
                return this._layers[this._layers.length - 1];
            }
            unregisterLayer(layer) {
                var i = this._layers.indexOf(layer);
                if (i < 0)
                    return;
                this._layers.splice(i, 1);
                this.layerTrigger(Container.LayerEventType.REMOVED, layer);
            }
            registerLayer(layer) {
                let lastModDefs = [];
                let messages = [];
                layer.monitor.onDirective((evt) => {
                    lastModDefs = this.deterLastModDefs(evt.directive);
                    messages = this.deterMessages(evt.directive);
                });
                layer.monitor.onDirectiveExecuted((evt) => {
                    if (!layer.currentZone)
                        return;
                    if (lastModDefs.length > 0) {
                        layer.currentZone.lastModDefs = lastModDefs;
                    }
                    if (messages.length > 0 && !layer.currentZone.isLoading()) {
                        layer.currentZone.messageList.clear();
                        layer.currentZone.messageList.addAll(messages);
                    }
                });
                this._layers.push(layer);
                this.markCurrent();
            }
            deterLastModDefs(directive) {
                let data = directive.getAdditionalData();
                if (!data || !data.rocketEvent || !data.rocketEvent.eiMods)
                    return [];
                let lastModDefs = [];
                let zoneClearer = new Cmd.ZoneClearer(this.getAllZones());
                let eiMods = data.rocketEvent.eiMods;
                for (let supremeEiTypeId in eiMods) {
                    if (!eiMods[supremeEiTypeId].pids && eiMods[supremeEiTypeId].draftIds) {
                        zoneClearer.clearBySupremeEiType(supremeEiTypeId, false);
                        continue;
                    }
                    if (eiMods[supremeEiTypeId].pids) {
                        for (let pid in eiMods[supremeEiTypeId].pids) {
                            let modType = eiMods[supremeEiTypeId].pids[pid];
                            switch (modType) {
                                case "changed":
                                    zoneClearer.clearByPid(supremeEiTypeId, pid, false);
                                    lastModDefs.push(Cmd.LastModDef.createLive(supremeEiTypeId, pid));
                                    break;
                                case "removed":
                                    zoneClearer.clearByPid(supremeEiTypeId, pid, true);
                                    break;
                                case "added":
                                    zoneClearer.clearBySupremeEiType(supremeEiTypeId, true);
                                    lastModDefs.push(Cmd.LastModDef.createLive(supremeEiTypeId, pid));
                                    break;
                                default:
                                    throw new Error("Invalid mod type " + modType);
                            }
                        }
                    }
                    if (eiMods[supremeEiTypeId].draftIds) {
                        for (let draftIdStr in eiMods[supremeEiTypeId].draftIds) {
                            let draftId = parseInt(draftIdStr);
                            let modType = eiMods[supremeEiTypeId].draftIds[draftIdStr];
                            switch (modType) {
                                case "changed":
                                    zoneClearer.clearByDraftId(supremeEiTypeId, draftId, false);
                                    lastModDefs.push(Cmd.LastModDef.createDraft(supremeEiTypeId, draftId));
                                    break;
                                case "removed":
                                    zoneClearer.clearByDraftId(supremeEiTypeId, draftId, true);
                                    break;
                                case "added":
                                    zoneClearer.clearBySupremeEiType(supremeEiTypeId, true);
                                    lastModDefs.push(Cmd.LastModDef.createDraft(supremeEiTypeId, draftId));
                                    break;
                                default:
                                    throw new Error("Invalid mod type " + modType);
                            }
                        }
                    }
                }
                return lastModDefs;
            }
            deterMessages(directive) {
                let data = directive.getAdditionalData();
                if (!data || !data.rocketEvent || !data.rocketEvent.messages)
                    return [];
                let messages = [];
                for (let message of data.rocketEvent.messages) {
                    messages.push(new Cmd.Message(message.text, message.severity));
                }
                return messages;
            }
            createLayer(dependentZone = null) {
                var jqLayer = $("<div />", {
                    "class": "rocket-layer"
                });
                this.jqContainer.append(jqLayer);
                var layer = new Cmd.Layer(jqLayer, this._layers.length, this, Jhtml.Monitor.create(jqLayer.get(0), new Jhtml.History(), true));
                this.registerLayer(layer);
                var jqToolbar = $("<div />", {
                    "class": "rocket-layer-toolbar"
                });
                jqLayer.append(jqToolbar);
                var jqButton = $("<button />", {
                    "class": "btn btn-warning"
                }).append($("<i />", {
                    "class": "fa fa-times"
                })).click(function () {
                    layer.close();
                });
                jqToolbar.append(jqButton);
                var that = this;
                layer.on(Cmd.Layer.EventType.CLOSE, () => {
                    that.unregisterLayer(layer);
                    this.markCurrent();
                });
                layer.on(Cmd.Layer.EventType.SHOWED, () => {
                    this.markCurrent();
                });
                layer.on(Cmd.Layer.EventType.HIDDEN, () => {
                    this.markCurrent();
                });
                if (dependentZone === null) {
                    this.layerTrigger(Container.LayerEventType.ADDED, layer);
                    return layer;
                }
                let reopenable = false;
                dependentZone.on(Cmd.Zone.EventType.CLOSE, () => {
                    layer.close();
                });
                dependentZone.on(Cmd.Zone.EventType.CONTENT_CHANGED, () => {
                    layer.close();
                });
                dependentZone.on(Cmd.Zone.EventType.HIDE, () => {
                    reopenable = layer.visible;
                    layer.hide();
                });
                dependentZone.on(Cmd.Zone.EventType.SHOW, () => {
                    if (!reopenable)
                        return;
                    layer.show();
                });
                this.layerTrigger(Container.LayerEventType.ADDED, layer);
                return layer;
            }
            getAllZones() {
                var zones = new Array();
                for (var i in this._layers) {
                    var layerZones = this._layers[i].zones;
                    for (var j in layerZones) {
                        zones.push(layerZones[j]);
                    }
                }
                return zones;
            }
            layerTrigger(eventType, layer) {
                var container = this;
                this.layerCallbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(layer);
                });
            }
            layerOn(eventType, callback) {
                this.layerCallbackRegistery.register(eventType.toString(), callback);
            }
            layerOff(eventType, callback) {
                this.layerCallbackRegistery.unregister(eventType.toString(), callback);
            }
        }
        Cmd.Container = Container;
        (function (Container) {
            let LayerEventType;
            (function (LayerEventType) {
                LayerEventType[LayerEventType["REMOVED"] = 0] = "REMOVED";
                LayerEventType[LayerEventType["ADDED"] = 1] = "ADDED";
            })(LayerEventType = Container.LayerEventType || (Container.LayerEventType = {}));
        })(Container = Cmd.Container || (Cmd.Container = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class ZoneClearer {
            constructor(zones) {
                this.zones = zones;
            }
            clearBySupremeEiType(supremeEiTypeId, restrictToCollections) {
                for (let zone of this.zones) {
                    if (!zone.page || zone.page.config.frozen || zone.page.disposed) {
                        continue;
                    }
                    if (!restrictToCollections) {
                        if (Rocket.Display.Entry.hasSupremeEiTypeId(zone.jQuery, supremeEiTypeId)) {
                            zone.page.dispose();
                        }
                        return;
                    }
                    if (Rocket.Display.Collection.hasSupremeEiTypeId(zone.jQuery, supremeEiTypeId)) {
                        zone.page.dispose();
                    }
                }
            }
            clearByPid(supremeEiTypeId, pid, remove) {
                for (let zone of this.zones) {
                    if (!zone.page || zone.page.disposed)
                        continue;
                    if (remove && this.removeByPid(zone, supremeEiTypeId, pid)) {
                        continue;
                    }
                    if (zone.page.config.frozen)
                        continue;
                    let jqElem = zone.jQuery;
                    if (Rocket.Display.Entry.hasPid(zone.jQuery, supremeEiTypeId, pid)) {
                        zone.page.dispose();
                    }
                }
            }
            removeByPid(zone, supremeEiTypeId, pid) {
                let entries = Rocket.Display.Entry.findByPid(zone.jQuery, supremeEiTypeId, pid);
                if (entries.length == 0)
                    return true;
                let success = true;
                for (let entry of entries) {
                    if (entry.collection) {
                        entry.dispose();
                    }
                    else {
                        success = false;
                    }
                }
                return success;
            }
            clearByDraftId(supremeEiTypeId, draftId, remove) {
                for (let zone of this.zones) {
                    if (!zone.page || zone.page.disposed)
                        continue;
                    if (remove && this.removeByDraftId(zone, supremeEiTypeId, draftId)) {
                        continue;
                    }
                    if (zone.page.config.frozen)
                        continue;
                    if (Rocket.Display.Entry.hasDraftId(zone.jQuery, supremeEiTypeId, draftId)) {
                        zone.page.dispose();
                    }
                }
            }
            removeByDraftId(zone, supremeEiTypeId, draftId) {
                let entries = Rocket.Display.Entry.findByDraftId(zone.jQuery, supremeEiTypeId, draftId);
                if (entries.length == 0)
                    return true;
                let success = true;
                for (let entry of entries) {
                    if (entry.collection) {
                        entry.dispose();
                    }
                    else {
                        success = false;
                    }
                }
                return success;
            }
        }
        Cmd.ZoneClearer = ZoneClearer;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class AdditionalTabManager {
            constructor(context) {
                this.jqAdditional = null;
                this.context = context;
                this.tabs = new Array();
            }
            createTab(title, prepend = false, severity = null) {
                this.setupAdditional();
                var jqNavItem = $("<li />", {
                    "text": title
                });
                if (severity) {
                    jqNavItem.addClass("rocket-severity-" + severity);
                }
                var jqContent = $("<div />", {
                    "class": "rocket-additional-content"
                });
                if (prepend) {
                    this.jqAdditional.find(".rocket-additional-nav").prepend(jqNavItem);
                }
                else {
                    this.jqAdditional.find(".rocket-additional-nav").append(jqNavItem);
                }
                this.jqAdditional.find(".rocket-additional-container").append(jqContent);
                var tab = new AdditionalTab(jqNavItem, jqContent);
                this.tabs.push(tab);
                var that = this;
                tab.onShow(function () {
                    for (var i in that.tabs) {
                        if (that.tabs[i] === tab)
                            continue;
                        this.tabs[i].hide();
                    }
                });
                tab.onDispose(function () {
                    that.removeTab(tab);
                });
                if (this.tabs.length == 1) {
                    tab.show();
                }
                return tab;
            }
            removeTab(tab) {
                for (var i in this.tabs) {
                    if (this.tabs[i] !== tab)
                        continue;
                    this.tabs.splice(parseInt(i), 1);
                    if (this.tabs.length == 0) {
                        this.setdownAdditional();
                        return;
                    }
                    if (tab.isActive()) {
                        this.tabs[0].show();
                    }
                    return;
                }
            }
            setupAdditional() {
                if (this.jqAdditional !== null)
                    return;
                var jqPage = this.context.jQuery;
                jqPage.addClass("rocket-contains-additional");
                this.jqAdditional = $("<div />", {
                    "class": "rocket-additional"
                });
                this.jqAdditional.append($("<ul />", { "class": "rocket-additional-nav" }));
                this.jqAdditional.append($("<div />", { "class": "rocket-additional-container" }));
                jqPage.append(this.jqAdditional);
            }
            setdownAdditional() {
                if (this.jqAdditional === null)
                    return;
                this.context.jQuery.removeClass("rocket-contains-additional");
                this.jqAdditional.remove();
                this.jqAdditional = null;
            }
        }
        Cmd.AdditionalTabManager = AdditionalTabManager;
        class AdditionalTab {
            constructor(jqNavItem, jqContent) {
                this.active = false;
                this.onShowCallbacks = [];
                this.onHideCallbacks = [];
                this.onDisposeCallbacks = [];
                this.jqNavItem = jqNavItem;
                this.jqContent = jqContent;
                this.jqNavItem.click(this.show);
                this.jqContent.hide();
            }
            getJqNavItem() {
                return this.jqNavItem;
            }
            getJqContent() {
                return this.jqContent;
            }
            isActive() {
                return this.active;
            }
            show() {
                this.active = true;
                this.jqNavItem.addClass("rocket-active");
                this.jqContent.show();
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
            }
            hide() {
                this.active = false;
                this.jqContent.hide();
                this.jqNavItem.removeClass("rocket-active");
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
            }
            dispose() {
                this.jqNavItem.remove();
                this.jqContent.remove();
                for (var i in this.onDisposeCallbacks) {
                    this.onDisposeCallbacks[i](this);
                }
            }
            onShow(callback) {
                this.onShowCallbacks.push(callback);
            }
            onHide(callback) {
                this.onHideCallbacks.push(callback);
            }
            onDispose(callback) {
                this.onDisposeCallbacks.push(callback);
            }
        }
        Cmd.AdditionalTab = AdditionalTab;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class LastModDef {
            static createLive(supremeEiTypeId, pid) {
                let lmd = new LastModDef();
                lmd.supremeEiTypeId = supremeEiTypeId;
                lmd.pid = pid;
                return lmd;
            }
            static createDraft(supremeEiTypeId, draftId) {
                let lmd = new LastModDef();
                lmd.supremeEiTypeId = supremeEiTypeId;
                lmd.draftId = draftId;
                return lmd;
            }
            static fromEntry(entry) {
                let lastModDef = new LastModDef();
                lastModDef.supremeEiTypeId = entry.supremeEiTypeId;
                lastModDef.pid = entry.pid;
                lastModDef.draftId = entry.draftId;
                return lastModDef;
            }
        }
        Cmd.LastModDef = LastModDef;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Lock {
            constructor(releaseCallback) {
                this.releaseCallback = releaseCallback;
            }
            release() {
                this.releaseCallback(this);
            }
        }
        Cmd.Lock = Lock;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class Menu {
            constructor(context) {
                this._toolbar = null;
                this._mainCommandList = null;
                this._partialCommandList = null;
                this._asideCommandList = null;
                this.context = context;
            }
            clear() {
                this._toolbar = null;
            }
            get toolbar() {
                if (this._toolbar) {
                    return this._toolbar;
                }
                let jqToolbar = this.context.jQuery.find(".rocket-zone-toolbar:first");
                if (jqToolbar.length == 0) {
                    jqToolbar = $("<div />", { "class": "rocket-zone-toolbar" }).prependTo(this.context.jQuery);
                }
                return this._toolbar = new Rocket.Display.Toolbar(jqToolbar);
            }
            getCommandsJq() {
                var commandsJq = this.context.jQuery.find(".rocket-zone-commands:first");
                if (commandsJq.length == 0) {
                    commandsJq = $("<div />", {
                        "class": "rocket-zone-commands"
                    });
                    this.context.jQuery.append(commandsJq);
                }
                return commandsJq;
            }
            get zoneCommandsJq() {
                return this.getCommandsJq();
            }
            get partialCommandList() {
                if (this._partialCommandList !== null) {
                    return this._partialCommandList;
                }
                let mainCommandJq = this.mainCommandList.jQuery;
                var partialCommandsJq = mainCommandJq.children(".rocket-partial-commands:first");
                if (partialCommandsJq.length == 0) {
                    partialCommandsJq = $("<div />", { "class": "rocket-partial-commands" }).prependTo(mainCommandJq);
                }
                return this._partialCommandList = new Rocket.Display.CommandList(partialCommandsJq);
            }
            get mainCommandList() {
                if (this._mainCommandList !== null) {
                    return this._mainCommandList;
                }
                let commandsJq = this.getCommandsJq();
                let mainCommandsJq = commandsJq.children(".rocket-main-commands:first");
                if (mainCommandsJq.length == 0) {
                    mainCommandsJq = commandsJq.children("div:first");
                    mainCommandsJq.addClass("rocket-main-commands");
                }
                if (mainCommandsJq.length == 0) {
                    let contentsJq = commandsJq.children(":not(.rocket-aside-commands)");
                    mainCommandsJq = $("<div></div>", { class: "rocket-main-commands" }).appendTo(commandsJq);
                    mainCommandsJq.append(contentsJq);
                }
                return this._mainCommandList = new Rocket.Display.CommandList(mainCommandsJq);
            }
            get asideCommandList() {
                if (this._asideCommandList !== null) {
                    return this._asideCommandList;
                }
                this.mainCommandList;
                let commandsJq = this.getCommandsJq();
                let asideCommandsJq = commandsJq.children(".rocket-aside-commands:first");
                if (asideCommandsJq.length == 0) {
                    asideCommandsJq = $("<div />", { "class": "rocket-aside-commands" }).appendTo(commandsJq);
                }
                return this._asideCommandList = new Rocket.Display.CommandList(asideCommandsJq);
            }
        }
        Cmd.Menu = Menu;
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        class MessageList {
            constructor(zone) {
                this.zone = zone;
            }
            clear() {
                this.zone.jQuery.find("rocket-messages").remove();
            }
            severityToClassName(severity) {
                switch (severity) {
                    case Severity.ERROR:
                        return "alert-danger";
                    case Severity.INFO:
                        return "alert-info";
                    case Severity.WARN:
                        return "alert-warn";
                    case Severity.SUCCESS:
                        return "alert-success";
                    default:
                        throw new Error("Unkown severity: " + severity);
                }
            }
            getMessagesUlJqBySeverity(severity) {
                let zoneJq = this.zone.jQuery;
                let className = this.severityToClassName(severity);
                let messagesJq = zoneJq.find("ul.rocket-messages." + className);
                if (messagesJq.length > 0) {
                    return messagesJq;
                }
                messagesJq = $("<ul />", { "class": "rocket-messages alert " + className + " list-unstyled" });
                let contentJq = this.zone.jQuery.find(".rocket-content");
                if (contentJq.length > 0) {
                    messagesJq.insertBefore(contentJq);
                }
                else {
                    zoneJq.prepend(messagesJq);
                }
                return messagesJq;
            }
            add(message) {
                let liJq = $("<li></li>", { "text": message.text });
                liJq.hide();
                this.getMessagesUlJqBySeverity(message.severity).append(liJq);
                liJq.fadeIn();
            }
            addAll(messages) {
                for (let message of messages) {
                    this.add(message);
                }
            }
        }
        Cmd.MessageList = MessageList;
        class Message {
            constructor(text, severity) {
                this.text = text;
                this.severity = severity;
            }
        }
        Cmd.Message = Message;
        let Severity;
        (function (Severity) {
            Severity[Severity["SUCCESS"] = 1] = "SUCCESS";
            Severity[Severity["INFO"] = 2] = "INFO";
            Severity[Severity["WARN"] = 4] = "WARN";
            Severity[Severity["ERROR"] = 8] = "ERROR";
        })(Severity = Cmd.Severity || (Cmd.Severity = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Util;
    (function (Util) {
        class CallbackRegistry {
            constructor() {
                this.callbackMap = {};
            }
            register(nature, callback) {
                if (this.callbackMap[nature] === undefined) {
                    this.callbackMap[nature] = new Array();
                }
                this.callbackMap[nature].push(callback);
            }
            unregister(nature, callback) {
                if (this.callbackMap[nature] === undefined) {
                    return;
                }
                for (let i in this.callbackMap[nature]) {
                    if (this.callbackMap[nature][i] === callback) {
                        this.callbackMap[nature].splice(parseInt(i), 1);
                        return;
                    }
                }
            }
            filter(nature) {
                if (this.callbackMap[nature] === undefined) {
                    return new Array();
                }
                return this.callbackMap[nature];
            }
            clear(nature) {
                if (nature) {
                    this.callbackMap[nature] = [];
                    return;
                }
                this.callbackMap = {};
            }
        }
        Util.CallbackRegistry = CallbackRegistry;
        class ArgUtils {
            static valIsset(arg) {
                if (arg !== null && arg !== undefined)
                    return;
                throw new InvalidArgumentError("Invalid arg: " + arg);
            }
        }
        Util.ArgUtils = ArgUtils;
        class ElementUtils {
            static isControl(elem) {
                return !!Jhtml.Util.closest(elem, "a, button, input, textarea, select", true);
            }
        }
        Util.ElementUtils = ElementUtils;
        class InvalidArgumentError extends Error {
        }
        Util.InvalidArgumentError = InvalidArgumentError;
        class IllegalStateError extends Error {
            static assertTrue(arg, errMsg = null) {
                if (arg === true)
                    return;
                if (errMsg === null) {
                    errMsg = "Illegal state";
                }
                throw new Error(errMsg);
            }
        }
        Util.IllegalStateError = IllegalStateError;
        function escSelector(str) {
            return str.replace(/([ #;?%&,.+*~\':"!^$[\]()=>|\/@])/g, "\\$1");
        }
        Util.escSelector = escSelector;
    })(Util = Rocket.Util || (Rocket.Util = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class StructureElement {
            constructor(jqElem) {
                this.onShowCallbacks = [];
                this.onHideCallbacks = [];
                this.toolbar = null;
                this.highlightedParent = null;
                this.jqElem = jqElem;
                jqElem.data("rocketStructureElement", this);
                this.valClasses();
            }
            valClasses() {
                if (this.isItem() || this.isGroup()) {
                    this.jqElem.removeClass("rocket-structure-element");
                }
                else {
                    this.jqElem.addClass("rocket-structure-element");
                }
            }
            get jQuery() {
                return this.jqElem;
            }
            get contentJq() {
                let contentJq = this.jqElem.children(".rocket-control");
                if (contentJq.length > 0) {
                    return contentJq;
                }
                return $("<div />", { "class": "rocket-control" }).appendTo(this.jqElem);
            }
            set type(type) {
                this.jqElem.removeClass("rocket-item");
                this.jqElem.removeClass("rocket-group");
                this.jqElem.removeClass("rocket-simple-group");
                this.jqElem.removeClass("rocket-light-group");
                this.jqElem.removeClass("rocket-main-group");
                this.jqElem.removeClass("rocket-panel");
                switch (type) {
                    case StructureElement.Type.ITEM:
                        this.jqElem.addClass("rocket-item");
                        break;
                    case StructureElement.Type.SIMPLE_GROUP:
                        this.jqElem.addClass("rocket-group");
                        this.jqElem.addClass("rocket-simple-group");
                        break;
                    case StructureElement.Type.LIGHT_GROUP:
                        this.jqElem.addClass("rocket-group");
                        this.jqElem.addClass("rocket-light-group");
                        break;
                    case StructureElement.Type.MAIN_GROUP:
                        this.jqElem.addClass("rocket-group");
                        this.jqElem.addClass("rocket-main-group");
                        break;
                    case StructureElement.Type.PANEL:
                        this.jqElem.addClass("rocket-panel");
                        break;
                }
                this.valClasses();
            }
            isGroup() {
                return this.jqElem.hasClass("rocket-group");
            }
            isPanel() {
                return this.jqElem.hasClass("rocket-panel");
            }
            isItem() {
                return this.jqElem.hasClass("rocket-item");
            }
            getToolbar(createIfNotExists) {
                if (!createIfNotExists || this.toolbar !== null) {
                    return this.toolbar;
                }
                let toolbarJq = this.jqElem.find(".rocket-toolbar:first")
                    .filter((index, elem) => {
                    return this === StructureElement.of($(elem));
                });
                if (toolbarJq.length == 0) {
                    toolbarJq = $("<div />", { "class": "rocket-toolbar" });
                    this.jqElem.prepend(toolbarJq);
                }
                return this.toolbar = new Toolbar(toolbarJq);
            }
            get title() {
                return this.jqElem.children("label:first").text();
            }
            set title(title) {
                let labelJq = this.jqElem.children("label:first");
                if (title === null) {
                    labelJq.remove();
                }
                if (labelJq.length > 0) {
                    labelJq.text(title);
                    return;
                }
                this.jqElem.prepend($("<label />", { text: title }));
            }
            getParent() {
                return StructureElement.of(this.jqElem.parent());
            }
            isVisible() {
                return this.jqElem.is(":visible");
            }
            show(includeParents = false) {
                for (var i in this.onShowCallbacks) {
                    this.onShowCallbacks[i](this);
                }
                this.jqElem.show();
                var parent;
                if (includeParents && null !== (parent = this.getParent())) {
                    parent.show(true);
                }
            }
            hide() {
                for (var i in this.onHideCallbacks) {
                    this.onHideCallbacks[i](this);
                }
                this.jqElem.hide();
            }
            onShow(callback) {
                this.onShowCallbacks.push(callback);
            }
            onHide(callback) {
                this.onHideCallbacks.push(callback);
            }
            scrollTo() {
                var top = this.jqElem.offset().top;
                var maxOffset = top - 50;
                var height = this.jqElem.outerHeight();
                var margin = $(window).height() - height;
                var offset = top - (margin / 2);
                if (maxOffset < offset) {
                    offset = maxOffset;
                }
                $("html, body").animate({
                    "scrollTop": offset
                }, 250);
            }
            highlight(findVisibleParent = false) {
                this.jqElem.addClass("rocket-highlighted");
                this.jqElem.removeClass("rocket-highlight-remember");
                if (!findVisibleParent || this.isVisible())
                    return;
                this.highlightedParent = this;
                while (null !== (this.highlightedParent = this.highlightedParent.getParent())) {
                    if (!this.highlightedParent.isVisible())
                        continue;
                    this.highlightedParent.highlight();
                    return;
                }
            }
            unhighlight(slow = false) {
                this.jqElem.removeClass("rocket-highlighted");
                if (slow) {
                    this.jqElem.addClass("rocket-highlight-remember");
                }
                else {
                    this.jqElem.removeClass("rocket-highlight-remember");
                }
                if (this.highlightedParent !== null) {
                    this.highlightedParent.unhighlight();
                    this.highlightedParent = null;
                }
            }
            static from(jqElem, create = false) {
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement)
                    return structureElement;
                if (!create)
                    return null;
                structureElement = new StructureElement(jqElem);
                jqElem.data("rocketStructureElement", structureElement);
                return structureElement;
            }
            static of(jqElem) {
                jqElem = jqElem.closest(".rocket-structure-element, .rocket-group, .rocket-item, .rocket-panel");
                if (jqElem.length == 0)
                    return null;
                var structureElement = jqElem.data("rocketStructureElement");
                if (structureElement instanceof StructureElement) {
                    return structureElement;
                }
                structureElement = StructureElement.from(jqElem, true);
                jqElem.data("rocketStructureElement", structureElement);
                return structureElement;
            }
            static findFirst(containerJq) {
                let elemsJq = containerJq.find(".rocket-structure-element, .rocket-group, .rocket-item, .rocket-panel").first();
                if (elemsJq.length == 0)
                    return null;
                var structureElement = elemsJq.data("rocketStructureElement");
                if (structureElement instanceof StructureElement) {
                    return structureElement;
                }
                structureElement = StructureElement.from(elemsJq, true);
                elemsJq.data("rocketStructureElement", structureElement);
                return structureElement;
            }
        }
        Display.StructureElement = StructureElement;
        (function (StructureElement) {
            let Type;
            (function (Type) {
                Type[Type["ITEM"] = 0] = "ITEM";
                Type[Type["SIMPLE_GROUP"] = 1] = "SIMPLE_GROUP";
                Type[Type["MAIN_GROUP"] = 2] = "MAIN_GROUP";
                Type[Type["LIGHT_GROUP"] = 3] = "LIGHT_GROUP";
                Type[Type["PANEL"] = 4] = "PANEL";
                Type[Type["NONE"] = 5] = "NONE";
            })(Type = StructureElement.Type || (StructureElement.Type = {}));
        })(StructureElement = Display.StructureElement || (Display.StructureElement = {}));
        class Toolbar {
            constructor(jqToolbar) {
                this.jqToolbar = jqToolbar;
                this.jqControls = jqToolbar.children(".rocket-group-controls").first();
                if (this.jqControls.length == 0) {
                    this.jqControls = $("<div />", { "class": "rocket-group-controls" });
                    this.jqToolbar.append(this.jqControls);
                    this.jqControls.hide();
                }
                else if (this.jqControls.is(':empty')) {
                    this.jqControls.hide();
                }
                var jqCommands = jqToolbar.children(".rocket-simple-commands");
                if (jqCommands.length == 0) {
                    jqCommands = $("<div />", { "class": "rocket-simple-commands" });
                    jqToolbar.append(jqCommands);
                }
                this.commandList = new CommandList(jqCommands, true);
            }
            get jQuery() {
                return this.jqToolbar;
            }
            getJqControls() {
                return this.jqControls;
            }
            getCommandList() {
                return this.commandList;
            }
            isEmpty() {
                return this.jqControls.is(":empty") && this.commandList.isEmpty();
            }
            show() {
                this.jQuery.show();
                return this;
            }
            hide() {
                this.jQuery.hide();
                return this;
            }
        }
        Display.Toolbar = Toolbar;
        class CommandList {
            constructor(jqCommandList, simple = false) {
                this.simple = simple;
                this.jqCommandList = jqCommandList;
                if (simple) {
                    jqCommandList.addClass("rocket-simple-commands");
                }
            }
            get jQuery() {
                return this.jqCommandList;
            }
            isEmpty() {
                return this.jqCommandList.is(":empty");
            }
            createJqCommandButton(buttonConfig, prepend = false) {
                this.jqCommandList.show();
                if (buttonConfig.iconType === undefined) {
                    buttonConfig.iconType = "fa fa-circle-o";
                }
                if (buttonConfig.severity === undefined) {
                    buttonConfig.severity = Display.Severity.SECONDARY;
                }
                var jqButton = $("<button />", {
                    "class": "btn btn-" + buttonConfig.severity
                        + (buttonConfig.important ? " rocket-important" : "")
                        + (buttonConfig.iconImportant ? " rocket-icon-important" : "")
                        + (buttonConfig.labelImportant ? " rocket-label-important" : ""),
                    "title": buttonConfig.tooltip,
                    "type": "button"
                });
                if (this.simple) {
                    jqButton.append($("<span />", {
                        "text": buttonConfig.label
                    })).append($("<i />", {
                        "class": buttonConfig.iconType
                    }));
                }
                else {
                    jqButton.append($("<i />", {
                        "class": buttonConfig.iconType
                    })).append("&nbsp;").append($("<span />", {
                        "text": buttonConfig.label
                    }));
                }
                if (prepend) {
                    this.jqCommandList.prepend(jqButton);
                }
                else {
                    this.jqCommandList.append(jqButton);
                }
                return jqButton;
            }
            static create(simple = false) {
                return new CommandList($("<div />"), simple);
            }
        }
        Display.CommandList = CommandList;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Cmd;
    (function (Cmd) {
        var util = Rocket.Util;
        class Zone {
            constructor(jqZone, url, layer) {
                this.urls = [];
                this.callbackRegistery = new util.CallbackRegistry();
                this._blocked = false;
                this._page = null;
                this._lastModDefs = [];
                this.locks = new Array();
                this.jqZone = jqZone;
                this.urls.push(this._activeUrl = url);
                this._layer = layer;
                jqZone.addClass("rocket-zone");
                jqZone.data("rocketZone", this);
                this.reset();
                this.hide();
            }
            get layer() {
                return this._layer;
            }
            get jQuery() {
                return this.jqZone;
            }
            get page() {
                return this._page;
            }
            set page(page) {
                if (this._page) {
                    throw new Error("page already assigned");
                }
                this._page = page;
                if (page) {
                    this.registerPageListeners();
                }
            }
            registerPageListeners() {
                this.page.on("disposed", this.onDisposed = () => {
                    this.clear(true);
                });
                this.page.on("promiseAssigned", this.onPromiseAssigned = () => {
                    this.clear(true);
                });
            }
            unregisterPageListeners() {
                if (this.onDisposed) {
                    this.page.off("disposed", this.onDisposed);
                }
                if (this.onPromiseAssigned) {
                    this.page.off("promiseAssigned", this.onPromiseAssigned);
                }
            }
            containsUrl(url) {
                for (var i in this.urls) {
                    if (this.urls[i].equals(url))
                        return true;
                }
                return false;
            }
            get activeUrl() {
                return this._activeUrl;
            }
            fireEvent(eventType) {
                var that = this;
                this.callbackRegistery.filter(eventType.toString()).forEach(function (callback) {
                    callback(that);
                });
            }
            ensureNotClosed() {
                if (this.jqZone !== null)
                    return;
                throw new Error("Page already closed.");
            }
            get closed() {
                return !this.jqZone;
            }
            close() {
                this.trigger(Zone.EventType.CLOSE);
                this.jqZone.remove();
                this.jqZone = null;
                if (this.page) {
                    this.unregisterPageListeners();
                    this.page.dispose();
                    this._page = null;
                }
            }
            show() {
                this.trigger(Zone.EventType.SHOW);
                this.jqZone.show();
            }
            hide() {
                this.trigger(Zone.EventType.HIDE);
                this.jqZone.hide();
            }
            reset() {
                this.additionalTabManager = new Cmd.AdditionalTabManager(this);
                this._menu = new Cmd.Menu(this);
                this._messageList = new Cmd.MessageList(this);
            }
            get empty() {
                return this.jqZone.is(":empty");
            }
            clear(showLoader = false) {
                if (showLoader) {
                    this.jqZone.addClass("rocket-loader");
                }
                else {
                    this.endLoading();
                }
                if (this.empty)
                    return;
                this.reset();
                this.jqZone.empty();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            applyHtml(html) {
                this.clear(false);
                this.jqZone.html(html);
                this.reset();
                this.applyLastModDefs();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            applyComp(comp) {
                this.clear(false);
                comp.attachTo(this.jqZone.get(0));
                this.reset();
                this.applyLastModDefs();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            isLoading() {
                return this.jqZone.hasClass("rocket-loader");
            }
            endLoading() {
                this.jqZone.removeClass("rocket-loader");
            }
            applyContent(jqContent) {
                this.endLoading();
                this.jqZone.append(jqContent);
                this.reset();
                this.trigger(Zone.EventType.CONTENT_CHANGED);
            }
            set lastModDefs(lastModDefs) {
                this._lastModDefs = lastModDefs;
                this.applyLastModDefs();
            }
            get lastModDefs() {
                return this._lastModDefs;
            }
            applyLastModDefs() {
                if (!this.jQuery)
                    return;
                this.chLastMod(Rocket.Display.Entry.findLastMod(this.jQuery), false);
                for (let lastModDef of this._lastModDefs) {
                    if (lastModDef.pid) {
                        this.chLastMod(Rocket.Display.Entry
                            .findByPid(this.jQuery, lastModDef.supremeEiTypeId, lastModDef.pid), true);
                        continue;
                    }
                    if (lastModDef.draftId) {
                        this.chLastMod(Rocket.Display.Entry
                            .findByDraftId(this.jQuery, lastModDef.supremeEiTypeId, lastModDef.draftId), true);
                        continue;
                    }
                    this.chLastMod(Rocket.Display.Entry.findBySupremeEiTypeId(this.jQuery, lastModDef.supremeEiTypeId), true);
                }
            }
            chLastMod(entries, lastMod) {
                for (let entry of entries) {
                    entry.lastMod = lastMod;
                }
            }
            trigger(eventType) {
                var context = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(context);
                });
            }
            on(eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            }
            off(eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            }
            createAdditionalTab(title, prepend = false, severity = null) {
                return this.additionalTabManager.createTab(title, prepend, severity);
            }
            get menu() {
                return this._menu;
            }
            get messageList() {
                return this._messageList;
            }
            get locked() {
                return this.locks.length > 0;
            }
            releaseLock(lock) {
                let i = this.locks.indexOf(lock);
                if (i == -1)
                    return;
                this.locks.splice(i, 1);
                this.trigger(Zone.EventType.BLOCKED_CHANGED);
            }
            createLock() {
                var that = this;
                var lock = new Cmd.Lock(function (lock) {
                    that.releaseLock(lock);
                });
                this.locks.push(lock);
                this.trigger(Zone.EventType.BLOCKED_CHANGED);
                return lock;
            }
            static of(jqElem) {
                if (!jqElem.hasClass(".rocket-zone")) {
                    jqElem = jqElem.parents(".rocket-zone");
                }
                let zone = jqElem.data("rocketZone");
                if (zone instanceof Zone)
                    return zone;
                return null;
            }
        }
        Cmd.Zone = Zone;
        (function (Zone) {
            let EventType;
            (function (EventType) {
                EventType[EventType["SHOW"] = 0] = "SHOW";
                EventType[EventType["HIDE"] = 1] = "HIDE";
                EventType[EventType["CLOSE"] = 2] = "CLOSE";
                EventType[EventType["CONTENT_CHANGED"] = 3] = "CONTENT_CHANGED";
                EventType[EventType["ACTIVE_URL_CHANGED"] = 4] = "ACTIVE_URL_CHANGED";
                EventType[EventType["BLOCKED_CHANGED"] = 5] = "BLOCKED_CHANGED";
            })(EventType = Zone.EventType || (Zone.EventType = {}));
        })(Zone = Cmd.Zone || (Cmd.Zone = {}));
    })(Cmd = Rocket.Cmd || (Rocket.Cmd = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Core;
    (function (Core) {
        class CommandPrivilegeList {
            constructor(containerJq) {
                this.containerJq = containerJq;
            }
            listen() {
                this.containerJq.find('li').each((i, elem) => {
                    let cpl = new CpListener($(elem));
                    cpl.check();
                    cpl.listen();
                });
            }
        }
        Core.CommandPrivilegeList = CommandPrivilegeList;
        class CpListener {
            constructor(elemJq) {
                this.elemJq = elemJq;
                this.checkJq = this.elemJq.children("input[type=checkbox]");
                this.decendentChecksJq = this.elemJq.find("li input[type=checkbox]");
            }
            listen() {
                this.checkJq.change(() => {
                    this.check();
                });
            }
            check() {
                if (this.elemJq.is(":disabled"))
                    return;
                if (this.checkJq.is(":checked")) {
                    this.decendentChecksJq.prop("disabled", true);
                    this.decendentChecksJq.prop("checked", true);
                }
                else {
                    this.decendentChecksJq.prop("disabled", false);
                    this.decendentChecksJq.prop("checked", false);
                }
            }
        }
    })(Core = Rocket.Core || (Rocket.Core = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Core;
    (function (Core) {
        class MailLogEntry {
            constructor(containerJq) {
                this.containerJq = containerJq;
                this.expanded = false;
                this.contentJq = containerJq.children("dl:first");
                this.contentJq.hide();
            }
            listen() {
                this.containerJq.children("header:first").click(() => {
                    this.toggle();
                });
            }
            toggle() {
                if (this.expanded) {
                    this.minimize();
                }
                else {
                    this.expand();
                }
            }
            expand() {
                if (this.expanded)
                    return;
                this.contentJq.slideDown(100);
                this.expanded = true;
                this.containerJq.addClass("rocket-expaned");
            }
            minimize() {
                if (!this.expanded)
                    return;
                this.contentJq.slideUp(100);
                this.expanded = false;
                this.containerJq.removeClass("rocket-expaned");
            }
        }
        Core.MailLogEntry = MailLogEntry;
        class MailPaging {
            constructor(selectJq) {
                this.selectJq = selectJq;
            }
            listen() {
                this.selectJq.change(() => {
                    window.location.href = this.selectJq.val();
                });
            }
        }
        Core.MailPaging = MailPaging;
    })(Core = Rocket.Core || (Rocket.Core = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Core;
    (function (Core) {
        class PrivilegeForm {
            constructor(formJq) {
                this.formJq = formJq;
                this.unusedPrivileges = [];
            }
            setup() {
                this.formJq.find(".rocket-privilege").each((i, elem) => {
                    let p = new Privilege($(elem));
                    p.setup();
                    if (!p.used) {
                        this.unusedPrivileges.push(p);
                    }
                });
                this.addButtonJq = Rocket.Cmd.Zone.of(this.formJq).menu.mainCommandList
                    .createJqCommandButton({
                    label: this.formJq.data("rocket-add-privilege-label")
                })
                    .click(() => {
                    this.incrPrivileges();
                    this.updateButton();
                });
            }
            incrPrivileges() {
                if (this.unusedPrivileges.length == 0) {
                    return;
                }
                let privilege = this.unusedPrivileges.shift();
                privilege.used = true;
            }
            updateButton() {
                if (this.unusedPrivileges.length == 0) {
                    this.addButtonJq.find("span").text(this.formJq.data("rocket-save-first-info"));
                    this.addButtonJq.prop("disabled", true);
                }
            }
        }
        Core.PrivilegeForm = PrivilegeForm;
        class Privilege {
            constructor(containerJq) {
                this.structureElement = Rocket.Display.StructureElement.from(containerJq, true);
                this.enablerJq = containerJq.find("input.rocket-privilege-enabler");
                this.restrictionsJq = containerJq.find(".rocket-restrictions:first");
                this.restrictionsEnablerJq = containerJq.find(".rocket-restrictions-enabler:first");
                this.restrictionsEnablerJq.on("change", () => {
                    this.checkVisibility();
                });
            }
            get used() {
                return this.enablerJq.is(":checked");
            }
            set used(used) {
                this.enablerJq.prop("checked", used);
                this.checkVisibility();
            }
            checkVisibility() {
                if (this.used) {
                    this.structureElement.show(false);
                }
                else {
                    this.structureElement.hide();
                }
                if (this.restrictionsEnablerJq.is(":checked")) {
                    this.restrictionsJq.show();
                }
                else {
                    this.restrictionsJq.hide();
                }
            }
            setup() {
                this.enablerJq.hide();
                this.checkVisibility();
                this.structureElement.getToolbar(true).show().getCommandList()
                    .createJqCommandButton({
                    iconType: "fa fa-trash",
                    label: "Remove"
                })
                    .click(() => {
                    this.used = false;
                    this.structureElement.contentJq.remove();
                });
            }
        }
        Core.Privilege = Privilege;
    })(Core = Rocket.Core || (Rocket.Core = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Collection {
            constructor(elemJq) {
                this.elemJq = elemJq;
                this.entryMap = {};
                this.selectorObservers = [];
                this.selectionChangedCbr = new Jhtml.Util.CallbackRegistry();
                this.insertCbr = new Jhtml.Util.CallbackRegistry();
                this.insertedCbr = new Jhtml.Util.CallbackRegistry();
                this._sortable = false;
            }
            scan() {
                this.sortedEntries = null;
                for (let entry of Display.Entry.children(this.elemJq)) {
                    if (this.entryMap[entry.id] && this.entryMap[entry.id] === entry) {
                        continue;
                    }
                    this.registerEntry(entry);
                }
            }
            registerEntry(entry) {
                this.entryMap[entry.id] = entry;
                if (entry.selector) {
                    for (let selectorObserver of this.selectorObservers) {
                        selectorObserver.observeEntrySelector(entry.selector);
                    }
                }
                if (this.sortable && entry.selector) {
                    this.applyHandle(entry.selector);
                }
                entry.selector.onChanged(() => {
                    this.triggerChanged();
                });
                var onFunc = () => {
                    if (this.entryMap[entry.id] !== entry)
                        return;
                    delete this.entryMap[entry.id];
                };
                entry.on(Display.Entry.EventType.DISPOSED, onFunc);
                entry.on(Display.Entry.EventType.REMOVED, onFunc);
            }
            triggerChanged() {
                this.selectionChangedCbr.fire();
            }
            onSelectionChanged(callback) {
                this.selectionChangedCbr.on(callback);
            }
            offSelectionChanged(callback) {
                this.selectionChangedCbr.off(callback);
            }
            setupSelector(selectorObserver) {
                this.selectorObservers.push(selectorObserver);
                for (let entry of this.entries) {
                    if (!entry.selector)
                        continue;
                    selectorObserver.observeEntrySelector(entry.selector);
                }
            }
            destroySelectors() {
                let selectorObserver;
                while (selectorObserver = this.selectorObservers.pop()) {
                    selectorObserver.destroy();
                }
            }
            get selectedIds() {
                let ids = [];
                for (let entry of this.entries) {
                    if (entry.selector && entry.selector.selected) {
                        ids.push(entry.id);
                    }
                }
                return ids;
            }
            get selectable() {
                return this.selectorObservers.length > 0;
            }
            get jQuery() {
                return this.elemJq;
            }
            containsEntryId(id) {
                return this.entryMap[id] !== undefined;
            }
            get entries() {
                if (this.sortedEntries) {
                    return this.sortedEntries;
                }
                this.sortedEntries = new Array();
                for (let entry of Display.Entry.children(this.elemJq)) {
                    if (!this.entryMap[entry.id] || this.entryMap[entry.id] !== entry) {
                        continue;
                    }
                    this.sortedEntries.push(entry);
                }
                return this.sortedEntries.slice();
            }
            get selectedEntries() {
                var entries = new Array();
                for (let entry of this.entries) {
                    if (!entry.selector || !entry.selector.selected)
                        continue;
                    entries.push(entry);
                }
                return entries;
            }
            setupSortable() {
                if (this._sortable)
                    return;
                this._sortable = true;
                this.elemJq.sortable({
                    "handle": ".rocket-handle",
                    "forcePlaceholderSize": true,
                    "placeholder": "rocket-entry-placeholder",
                    "start": (event, ui) => {
                        let entry = Display.Entry.find(ui.item, true);
                        this.insertCbr.fire([entry]);
                    },
                    "update": (event, ui) => {
                        this.sortedEntries = null;
                        let entry = Display.Entry.find(ui.item, true);
                        this.insertedCbr.fire([entry], this.findPreviousEntry(entry), this.findNextEntry(entry));
                    }
                });
                for (let entry of this.entries) {
                    if (!entry.selector)
                        continue;
                    this.applyHandle(entry.selector);
                }
            }
            get sortable() {
                return this._sortable;
            }
            applyHandle(selector) {
                selector.jQuery.append($("<div />", { "class": "rocket-handle" })
                    .append($("<i></i>", { "class": "fa fa-bars" })));
            }
            enabledSortable() {
                this._sortable = true;
                this.elemJq.sortable("enable");
                this.elemJq.disableSelection();
            }
            disableSortable() {
                this._sortable = false;
                this.elemJq.sortable("disable");
                this.elemJq.enableSelection();
            }
            valEntry(entry) {
                let id = entry.id;
                if (!this.entryMap[id]) {
                    throw new Error("Unknown entry with id " + id);
                }
                if (this.entryMap[id] !== entry) {
                    throw new Error("Collection contains other entry with same id: " + id);
                }
            }
            containsEntry(entry) {
                let id = entry.id;
                return !!this.entryMap[id] && this.entryMap[id] === entry;
            }
            findPreviousEntry(nextEntry) {
                this.valEntry(nextEntry);
                let aboveEntry = null;
                for (let entry of this.entries) {
                    if (entry === nextEntry)
                        return aboveEntry;
                    aboveEntry = entry;
                }
                return null;
            }
            findPreviousEntries(previousEntry) {
                this.valEntry(previousEntry);
                let previousEntries = [];
                for (let entry of this.entries) {
                    if (entry === previousEntry) {
                        return previousEntries;
                    }
                    previousEntries.push(entry);
                }
                return previousEntries;
            }
            findNextEntry(previousEntry) {
                this.valEntry(previousEntry);
                for (let entry of this.entries) {
                    if (!previousEntry) {
                        return entry;
                    }
                    if (entry === previousEntry) {
                        previousEntry = null;
                    }
                }
                return null;
            }
            findNextEntries(beforeEntry) {
                this.valEntry(beforeEntry);
                let nextEntries = [];
                for (let entry of this.entries) {
                    if (!beforeEntry) {
                        nextEntries.push(entry);
                    }
                    if (entry === beforeEntry) {
                        beforeEntry = null;
                    }
                }
                return nextEntries;
            }
            findTreeParents(baseEntry) {
                this.valTreeEntry(baseEntry);
                let parentEntries = [];
                if (baseEntry.treeLevel === null) {
                    return parentEntries;
                }
                let curTreeLevel = baseEntry.treeLevel;
                for (let entry of this.findPreviousEntries(baseEntry).reverse()) {
                    let treeLevel = entry.treeLevel;
                    if (treeLevel === null) {
                        return parentEntries;
                    }
                    if (treeLevel < curTreeLevel) {
                        parentEntries.push(entry);
                        curTreeLevel = entry.treeLevel;
                    }
                    if (treeLevel == 0) {
                        return parentEntries;
                    }
                }
                return parentEntries;
            }
            valTreeEntry(entry) {
                if (entry.treeLevel === null) {
                    throw new Error("Passed entry is not part of a tree.");
                }
            }
            findTreeDescendants(baseEntry) {
                this.valTreeEntry(baseEntry);
                let treeLevel = baseEntry.treeLevel;
                let treeDescendants = [];
                for (let entry of this.findNextEntries(baseEntry)) {
                    if (entry.treeLevel > treeLevel) {
                        treeDescendants.push(entry);
                        continue;
                    }
                    return treeDescendants;
                }
                return treeDescendants;
            }
            insertAfter(aboveEntry, entries) {
                if (aboveEntry !== null) {
                    this.valEntry(aboveEntry);
                }
                let belowEntry = this.findNextEntry(aboveEntry);
                this.insertCbr.fire(entries);
                for (let entry of entries.reverse()) {
                    if (aboveEntry) {
                        entry.jQuery.insertAfter(aboveEntry.jQuery);
                    }
                    else {
                        this.elemJq.prepend(entry.jQuery);
                    }
                }
                this.sortedEntries = null;
                this.insertedCbr.fire(entries, aboveEntry, belowEntry);
            }
            onInsert(callback) {
                this.insertCbr.on(callback);
            }
            offInsert(callback) {
                this.insertCbr.off(callback);
            }
            onInserted(callback) {
                this.insertedCbr.on(callback);
            }
            offInserted(callback) {
                this.insertedCbr.off(callback);
            }
            static test(jqElem) {
                if (jqElem.hasClass(Collection.CSS_CLASS)) {
                    return Collection.from(jqElem);
                }
                return null;
            }
            static from(jqElem) {
                var collection = jqElem.data("rocketCollection");
                if (collection instanceof Collection)
                    return collection;
                collection = new Collection(jqElem);
                jqElem.data("rocketCollection", collection);
                jqElem.addClass(Collection.CSS_CLASS);
                return collection;
            }
            static of(jqElem) {
                jqElem = jqElem.closest("." + Collection.CSS_CLASS);
                if (jqElem.length == 0)
                    return null;
                return Collection.from(jqElem);
            }
            static fromArr(entriesJq) {
                let collections = new Array();
                entriesJq.each(function () {
                    collections.push(Collection.from($(this)));
                });
                return collections;
            }
            static buildSupremeEiTypeISelector(supremeEiTypeId) {
                return "." + Collection.CSS_CLASS + "[" + Collection.SUPREME_EI_TYPE_ID_ATTR + "=" + supremeEiTypeId + "]";
            }
            static findBySupremeEiTypeId(jqContainer, supremeEiTypeId) {
                return Collection.fromArr(jqContainer.find(Collection.buildSupremeEiTypeISelector(supremeEiTypeId)));
            }
            static hasSupremeEiTypeId(jqContainer, supremeEiTypeId) {
                return 0 < jqContainer.has(Collection.buildSupremeEiTypeISelector(supremeEiTypeId)).length;
            }
        }
        Collection.CSS_CLASS = "rocket-collection";
        Collection.SUPREME_EI_TYPE_ID_ATTR = "data-rocket-supreme-ei-type-id";
        Display.Collection = Collection;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Command {
            constructor(jLink) {
                this._observing = false;
                this.confirm = null;
                this.jLink = jLink;
                jLink.onEvent((evt) => {
                    this.onEvent(evt);
                });
            }
            get jQuery() {
                return $(this.jLink.element);
            }
            onEvent(evt) {
                if (!this.confirm) {
                    this.confirm = Display.Confirm.test(this.jQuery);
                }
                if (!this.confirm) {
                    this.markAsLastMod();
                    return;
                }
                evt.preventExec();
                this.confirm.open();
                this.confirm.successCallback = () => {
                    this.markAsLastMod();
                    this.jLink.exec();
                };
            }
            markAsLastMod() {
                let entry = Display.Entry.of(this.jQuery);
                if (entry) {
                    Rocket.Cmd.Zone.of(this.jQuery).lastModDefs = [Rocket.Cmd.LastModDef.fromEntry(entry)];
                }
            }
            observe() {
                if (this._observing)
                    return;
                this._observing = true;
                this.jLink.onDirective((directivePromise) => {
                    this.handle(directivePromise);
                });
            }
            handle(directivePromise) {
                let jqElem = $(this.jLink.element);
                let iJq = jqElem.find("i");
                let orgClassAttr = iJq.attr("class");
                iJq.attr("class", "fa fa-circle-o-notch fa-spin");
                jqElem.css("cursor", "default");
                this.jLink.disabled = true;
                directivePromise.then(directive => {
                    iJq.attr("class", orgClassAttr);
                    this.jLink.disabled = false;
                    let revt = RocketEvent.fromAdditionalData(directive.getAdditionalData());
                    if (!revt.swapControlHtml)
                        return;
                    let jqNewElem = $(revt.swapControlHtml);
                    jqElem.replaceWith(jqNewElem);
                    this.jLink.dispose();
                    this.jLink = Jhtml.Ui.Link.from(jqNewElem.get(0));
                    this._observing = false;
                    this.observe();
                });
            }
        }
        Display.Command = Command;
        class RocketEvent {
            constructor() {
                this.swapControlHtml = null;
            }
            static fromAdditionalData(data) {
                let rocketEvent = new RocketEvent();
                if (!data || !data.rocketEvent) {
                    return rocketEvent;
                }
                if (data.rocketEvent.swapControlHtml) {
                    rocketEvent.swapControlHtml = data.rocketEvent.swapControlHtml;
                }
                return rocketEvent;
            }
        }
        Display.RocketEvent = RocketEvent;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Entry {
            constructor(elemJq) {
                this.elemJq = elemJq;
                this._selector = null;
                this._state = Entry.State.PERSISTENT;
                this.callbackRegistery = new Rocket.Util.CallbackRegistry();
                elemJq.on("remove", () => {
                    this.trigger(Entry.EventType.DISPOSED);
                    this.callbackRegistery.clear();
                });
                let selectorJq = elemJq.find(".rocket-entry-selector:first");
                if (selectorJq.length > 0) {
                    this.initSelector(selectorJq);
                }
            }
            get lastMod() {
                return this.elemJq.hasClass(Entry.LAST_MOD_CSS_CLASS);
            }
            set lastMod(lastMod) {
                if (lastMod) {
                    this.elemJq.addClass(Entry.LAST_MOD_CSS_CLASS);
                }
                else {
                    this.elemJq.removeClass(Entry.LAST_MOD_CSS_CLASS);
                }
            }
            get collection() {
                return Display.Collection.test(this.elemJq.parent());
            }
            initSelector(jqSelector) {
                this._selector = new Display.EntrySelector(jqSelector, this);
                var that = this;
                this.elemJq.click(function (e) {
                    if (getSelection().toString() || Rocket.Util.ElementUtils.isControl(e.target)) {
                        return;
                    }
                    that._selector.selected = !that._selector.selected;
                });
            }
            trigger(eventType) {
                var entry = this;
                this.callbackRegistery.filter(eventType.toString())
                    .forEach(function (callback) {
                    callback(entry);
                });
            }
            on(eventType, callback) {
                this.callbackRegistery.register(eventType.toString(), callback);
            }
            off(eventType, callback) {
                this.callbackRegistery.unregister(eventType.toString(), callback);
            }
            get jQuery() {
                return this.elemJq;
            }
            show() {
                this.elemJq.show();
            }
            hide() {
                this.elemJq.hide();
            }
            dispose() {
                this.elemJq.remove();
            }
            get state() {
                return this._state;
            }
            set state(state) {
                if (this._state == state)
                    return;
                this._state = state;
                if (state == Entry.State.REMOVED) {
                    this.trigger(Entry.EventType.REMOVED);
                }
            }
            get generalId() {
                return this.elemJq.data("rocket-general-id").toString();
            }
            get id() {
                if (this.draftId !== null) {
                    return this.draftId.toString();
                }
                return this.pid;
            }
            get supremeEiTypeId() {
                return this.elemJq.data("rocket-supreme-ei-type-id").toString();
            }
            get eiTypeId() {
                return this.elemJq.data("rocket-ei-type-id").toString();
            }
            get pid() {
                return this.elemJq.data("rocket-ei-id").toString();
            }
            get draftId() {
                var draftId = parseInt(this.elemJq.data("rocket-draft-id"));
                if (!isNaN(draftId)) {
                    return draftId;
                }
                return null;
            }
            get identityString() {
                return this.elemJq.data("rocket-identity-string");
            }
            get selector() {
                return this._selector;
            }
            findTreeLevelClass() {
                let cl = this.elemJq.get(0).classList;
                for (let i = 0; i < cl.length; i++) {
                    let className = cl.item(i);
                    if (className.startsWith(Entry.TREE_LEVEL_CSS_CLASS_PREFIX)) {
                        return className;
                    }
                }
                return null;
            }
            get treeLevel() {
                let className = this.findTreeLevelClass();
                if (className === null)
                    return null;
                return parseInt(className.substr(Entry.TREE_LEVEL_CSS_CLASS_PREFIX.length));
            }
            set treeLevel(treeLevel) {
                let className = this.findTreeLevelClass();
                if (className) {
                    this.elemJq.removeClass(className);
                }
                if (treeLevel !== null) {
                    this.elemJq.addClass(Entry.TREE_LEVEL_CSS_CLASS_PREFIX + treeLevel);
                }
            }
            static from(elemJq) {
                var entry = elemJq.data("rocketEntry");
                if (entry instanceof Entry) {
                    return entry;
                }
                entry = new Entry(elemJq);
                elemJq.data("rocketEntry", entry);
                elemJq.addClass(Entry.CSS_CLASS);
                return entry;
            }
            static of(jqElem) {
                var jqElem = jqElem.closest("." + Entry.CSS_CLASS);
                if (jqElem.length == 0)
                    return null;
                return Entry.from(jqElem);
            }
            static find(jqElem, includeSelf = false) {
                let entries = Entry.findAll(jqElem, includeSelf);
                if (entries.length > 0) {
                    return entries[0];
                }
                return null;
            }
            static findAll(jqElem, includeSelf = false) {
                let jqEntries = jqElem.find("." + Entry.CSS_CLASS);
                if (includeSelf) {
                    jqEntries = jqEntries.add(jqElem.filter("." + Entry.CSS_CLASS));
                }
                return Entry.fromArr(jqEntries);
            }
            static findLastMod(jqElem) {
                let entriesJq = jqElem.find("." + Entry.CSS_CLASS + "." + Entry.LAST_MOD_CSS_CLASS);
                return Entry.fromArr(entriesJq);
            }
            static fromArr(entriesJq) {
                let entries = new Array();
                entriesJq.each(function () {
                    entries.push(Entry.from($(this)));
                });
                return entries;
            }
            static children(jqElem) {
                return Entry.fromArr(jqElem.children("." + Entry.CSS_CLASS));
            }
            static filter(jqElem) {
                return Entry.fromArr(jqElem.filter("." + Entry.CSS_CLASS));
            }
            static buildSupremeEiTypeISelector(supremeEiTypeId) {
                return "." + Entry.CSS_CLASS + "[" + Entry.SUPREME_EI_TYPE_ID_ATTR + "=" + Rocket.Util.escSelector(supremeEiTypeId) + "]";
            }
            static findBySupremeEiTypeId(jqContainer, supremeEiTypeId) {
                return Entry.fromArr(jqContainer.find(Entry.buildSupremeEiTypeISelector(supremeEiTypeId)));
            }
            static hasSupremeEiTypeId(jqContainer, supremeEiTypeId) {
                return 0 < jqContainer.has(Entry.buildSupremeEiTypeISelector(supremeEiTypeId)).length;
            }
            static buildPidSelector(supremeEiTypeId, pid) {
                return "." + Entry.CSS_CLASS + "[" + Entry.SUPREME_EI_TYPE_ID_ATTR + "=" + Rocket.Util.escSelector(supremeEiTypeId) + "]["
                    + Entry.ID_REP_ATTR + "=" + Rocket.Util.escSelector(pid) + "]";
            }
            static findByPid(jqElem, supremeEiTypeId, pid) {
                return Entry.fromArr(jqElem.find(Entry.buildPidSelector(supremeEiTypeId, pid)));
            }
            static hasPid(jqElem, supremeEiTypeId, pid) {
                return 0 < jqElem.has(Entry.buildPidSelector(supremeEiTypeId, pid)).length;
            }
            static buildDraftIdSelector(supremeEiTypeId, draftId) {
                return "." + Entry.CSS_CLASS + "[" + Entry.SUPREME_EI_TYPE_ID_ATTR + "=" + Rocket.Util.escSelector(supremeEiTypeId) + "]["
                    + Entry.DRAFT_ID_ATTR + "=" + draftId + "]";
            }
            static findByDraftId(jqElem, supremeEiTypeId, draftId) {
                return Entry.fromArr(jqElem.find(Entry.buildDraftIdSelector(supremeEiTypeId, draftId)));
            }
            static hasDraftId(jqElem, supremeEiTypeId, draftId) {
                return 0 < jqElem.has(Entry.buildDraftIdSelector(supremeEiTypeId, draftId)).length;
            }
        }
        Entry.CSS_CLASS = "rocket-entry";
        Entry.TREE_LEVEL_CSS_CLASS_PREFIX = "rocket-tree-level-";
        Entry.LAST_MOD_CSS_CLASS = "rocket-last-mod";
        Entry.SUPREME_EI_TYPE_ID_ATTR = "data-rocket-supreme-ei-type-id";
        Entry.EI_TYPE_ID_ATTR = "data-rocket-ei-type-id";
        Entry.ID_REP_ATTR = "data-rocket-ei-id";
        Entry.DRAFT_ID_ATTR = "data-rocket-draft-id";
        Display.Entry = Entry;
        (function (Entry) {
            let State;
            (function (State) {
                State[State["PERSISTENT"] = 0] = "PERSISTENT";
                State[State["REMOVED"] = 1] = "REMOVED";
            })(State = Entry.State || (Entry.State = {}));
            let EventType;
            (function (EventType) {
                EventType[EventType["DISPOSED"] = 0] = "DISPOSED";
                EventType[EventType["REFRESHED"] = 1] = "REFRESHED";
                EventType[EventType["REMOVED"] = 2] = "REMOVED";
            })(EventType = Entry.EventType || (Entry.EventType = {}));
        })(Entry = Display.Entry || (Display.Entry = {}));
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class EntryForm {
            constructor(jqElem) {
                this.jqEiTypeSelect = null;
                this.inited = false;
                this.jqElem = jqElem;
            }
            init() {
                if (this.inited) {
                    throw new Error("EntryForm already initialized:");
                }
                this.inited = true;
                if (!this.jqElem.hasClass("rocket-multi-ei-type"))
                    return;
                let jqSelector = this.jqElem.children(".rocket-ei-type-selector");
                let se = Display.StructureElement.of(jqSelector);
                if (se && se.isGroup()) {
                    se.getToolbar(true).show().getJqControls().show().append(jqSelector);
                }
                else {
                    jqSelector.addClass("rocket-toolbar");
                }
                this.jqEiTypeSelect = jqSelector.find("select");
                this.updateDisplay();
                this.jqEiTypeSelect.change(() => {
                    this.updateDisplay();
                });
            }
            updateDisplay() {
                if (!this.jqEiTypeSelect)
                    return;
                this.jqElem.children(".rocket-ei-type-entry-form").hide();
                this.jqElem.children(".rocket-ei-type-" + this.jqEiTypeSelect.val()).show();
            }
            get jQuery() {
                return this.jqElem;
            }
            get multiEiType() {
                return this.jqEiTypeSelect ? true : false;
            }
            get curEiTypeId() {
                if (!this.multiEiType) {
                    return this.jqElem.data("rocket-ei-type-id");
                }
                return this.jqEiTypeSelect.val();
            }
            set curEiTypeId(typeId) {
                this.jqEiTypeSelect.val(typeId);
                this.updateDisplay();
            }
            get curGenericLabel() {
                if (!this.multiEiType) {
                    return this.jqElem.data("rocket-generic-label");
                }
                return this.jqEiTypeSelect.children(":selected").text();
            }
            get curGenericIconType() {
                if (!this.multiEiType) {
                    return this.jqElem.data("rocket-generic-icon-type");
                }
                return this.jqEiTypeSelect.data("rocket-generic-icon-types")[this.curEiTypeId];
            }
            get typeMap() {
                let typeMap = {};
                if (!this.multiEiType) {
                    typeMap[this.curEiTypeId] = this.curGenericLabel;
                    return typeMap;
                }
                this.jqEiTypeSelect.children().each(function () {
                    let jqElem = $(this);
                    typeMap[jqElem.attr("value")] = jqElem.text();
                });
                return typeMap;
            }
            static from(jqElem, create = true) {
                var entryForm = jqElem.data("rocketEntryForm");
                if (entryForm instanceof EntryForm)
                    return entryForm;
                if (!create)
                    return null;
                entryForm = new EntryForm(jqElem);
                entryForm.init();
                jqElem.data("rocketEntryForm", entryForm);
                return entryForm;
            }
            static firstOf(jqElem) {
                if (jqElem.hasClass("rocket-entry-form")) {
                    return EntryForm.from(jqElem);
                }
                let jqEntryForm = jqElem.find(".rocket-entry-form:first");
                if (jqEntryForm.length == 0)
                    return null;
                return EntryForm.from(jqEntryForm);
            }
            static find(jqElem, mulitTypeOnly = false) {
                let entryForms = [];
                jqElem.find(".rocket-entry-form" + (mulitTypeOnly ? ".rocket-multi-ei-type" : "")).each(function () {
                    entryForms.push(EntryForm.from($(this)));
                });
                return entryForms;
            }
        }
        Display.EntryForm = EntryForm;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class EntrySelector {
            constructor(jqElem, _entry) {
                this.jqElem = jqElem;
                this._entry = _entry;
                this.changedCallbacks = [];
                this._selected = false;
            }
            get jQuery() {
                return this.jqElem;
            }
            get entry() {
                return this._entry;
            }
            get selected() {
                return this._selected;
            }
            set selected(selected) {
                if (this._selected == selected)
                    return;
                this._selected = selected;
                this.triggerChanged();
            }
            onChanged(callback, prepend = false) {
                if (prepend) {
                    this.changedCallbacks.unshift(callback);
                }
                else {
                    this.changedCallbacks.push(callback);
                }
            }
            offChanged(callback) {
                this.changedCallbacks.splice(this.changedCallbacks.indexOf(callback));
            }
            triggerChanged() {
                this.changedCallbacks.forEach((callback) => {
                    callback(this);
                });
            }
        }
        Display.EntrySelector = EntrySelector;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Initializer {
            constructor(container, errorTabTitle, displayErrorLabel) {
                this.container = container;
                this.errorTabTitle = errorTabTitle;
                this.displayErrorLabel = displayErrorLabel;
                this.errorIndexes = new Array();
            }
            scan() {
                var errorIndex = null;
                while (undefined !== (errorIndex = this.errorIndexes.pop())) {
                    errorIndex.getTab().dispose();
                }
                var zones = this.container.getAllZones();
                for (var i in zones) {
                    this.scanPage(zones[i]);
                }
            }
            scanPage(context) {
                var that = this;
                var i = 0;
                var jqPage = context.jQuery;
                Display.EntryForm.find(jqPage, true);
                jqPage.find(".rocket-main-group").each(function () {
                    let elemJq = $(this);
                    Initializer.scanGroupNav(elemJq.parent());
                });
                var errorIndex = null;
                jqPage.find(".rocket-message-error").each(function () {
                    var structureElement = Display.StructureElement.of($(this));
                    if (errorIndex === null) {
                        errorIndex = new ErrorIndex(context.createAdditionalTab(that.errorTabTitle, false, Display.Severity.DANGER), that.displayErrorLabel);
                        that.errorIndexes.push(errorIndex);
                    }
                    errorIndex.addError(structureElement, $(this).text());
                });
            }
            static scanGroupNav(jqContainer) {
                let curGroupNav = null;
                jqContainer.children().each(function () {
                    var jqElem = $(this);
                    if (!jqElem.hasClass("rocket-main-group")) {
                        curGroupNav = null;
                        return;
                    }
                    if (curGroupNav === null) {
                        curGroupNav = GroupNav.fromMain(jqElem);
                    }
                    var group = Display.StructureElement.from(jqElem);
                    if (group === null) {
                        curGroupNav.registerGroup(Display.StructureElement.from(jqElem, true));
                    }
                });
                return curGroupNav;
            }
        }
        Display.Initializer = Initializer;
        class GroupNav {
            constructor(jqGroupNav) {
                this.jqGroupNav = jqGroupNav;
                this.groups = new Array();
                jqGroupNav.hide();
            }
            registerGroup(group) {
                this.groups.push(group);
                if (this.groups.length == 2) {
                    this.jqGroupNav.show();
                }
                let jqA = $("<a />", {
                    "text": group.title,
                    "class": "nav-link"
                });
                let jqLi = $("<li />", {
                    "class": "nav-item"
                }).append(jqA);
                this.jqGroupNav.append(jqLi);
                group.jQuery.children("label:first").hide();
                var that = this;
                jqLi.click(function () {
                    group.show();
                });
                group.onShow(function () {
                    jqLi.addClass("rocket-active");
                    jqA.addClass("active");
                    for (var i in that.groups) {
                        if (that.groups[i] !== group) {
                            that.groups[i].hide();
                        }
                    }
                });
                group.onHide(function () {
                    jqLi.removeClass("rocket-active");
                    jqA.removeClass("active");
                });
                if (this.groups.length == 1) {
                    group.show();
                }
                else {
                    group.hide();
                }
            }
            static fromMain(jqElem) {
                var jqPrev = jqElem.prev(".rocket-main-group-nav");
                if (jqPrev.length > 0) {
                    let groupNav = jqPrev.data("rocketGroupNav");
                    if (groupNav instanceof GroupNav)
                        return groupNav;
                }
                var ulJq = $("<ul />", { "class": "rocket-main-group-nav nav nav-tabs" }).insertBefore(jqElem);
                let groupNav = new GroupNav(ulJq);
                ulJq.data("rocketGroupNav", groupNav);
                return groupNav;
            }
        }
        class ErrorIndex {
            constructor(tab, displayErrorLabel) {
                this.tab = tab;
                this.displayErrorLabel = displayErrorLabel;
            }
            getTab() {
                return this.tab;
            }
            addError(structureElement, errorMessage) {
                var jqElem = $("<div />", {
                    "class": "rocket-error-index-entry"
                }).append($("<div />", {
                    "class": "rocket-error-index-message",
                    "text": errorMessage
                })).append($("<a />", {
                    "href": "#",
                    "text": this.displayErrorLabel
                }));
                this.tab.getJqContent().append(jqElem);
                var clicked = false;
                var visibleSe = null;
                if (!structureElement)
                    return;
                jqElem.mouseenter(function () {
                    structureElement.highlight(true);
                });
                jqElem.mouseleave(function () {
                    structureElement.unhighlight(clicked);
                    clicked = false;
                });
                jqElem.click(function (e) {
                    e.preventDefault();
                    clicked = true;
                    structureElement.show(true);
                    structureElement.scrollTo();
                });
            }
        }
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class MultiEntrySelectorObserver {
            constructor(originalPids = new Array()) {
                this.originalPids = originalPids;
                this.identityStrings = {};
                this.selectors = {};
                this.checkJqs = {};
                this.onChanged = (selector) => {
                    let id = selector.entry.id;
                    this.checkJqs[id].prop("checked", selector.selected);
                    this.chSelect(selector.selected, id);
                };
                this.onDisposed = (entry) => {
                    delete this.selectors[entry.id];
                };
                this.onRemoved = (entry) => {
                    this.chSelect(false, entry.id);
                };
                this.selectedIds = originalPids;
            }
            destroy() {
                for (let key in this.selectors) {
                    this.checkJqs[key].remove();
                    let selector = this.selectors[key];
                    selector.offChanged(this.onChanged);
                    let entry = selector.entry;
                    entry.off(Display.Entry.EventType.DISPOSED, this.onDisposed);
                    entry.off(Display.Entry.EventType.REMOVED, this.onRemoved);
                }
                this.identityStrings = {};
                this.selectors = {};
                this.checkJqs = {};
            }
            observeEntrySelector(selector) {
                let entry = selector.entry;
                let id = entry.id;
                if (this.selectors[id])
                    return;
                let jqCheck = $("<input />", { "type": "checkbox" });
                selector.jQuery.empty();
                selector.jQuery.append(jqCheck);
                jqCheck.change(() => {
                    selector.selected = jqCheck.is(":checked");
                });
                selector.onChanged(this.onChanged, true);
                selector.selected = this.containsSelectedId(id);
                jqCheck.prop("checked", selector.selected);
                this.checkJqs[id] = jqCheck;
                this.selectors[id] = selector;
                this.identityStrings[id] = entry.identityString;
                entry.on(Display.Entry.EventType.DISPOSED, this.onDisposed);
                entry.on(Display.Entry.EventType.REMOVED, this.onRemoved);
            }
            containsSelectedId(id) {
                return -1 < this.selectedIds.indexOf(id);
            }
            chSelect(selected, id) {
                if (selected) {
                    if (-1 < this.selectedIds.indexOf(id))
                        return;
                    this.selectedIds.push(id);
                    return;
                }
                var i;
                if (-1 < (i = this.selectedIds.indexOf(id))) {
                    this.selectedIds.splice(i, 1);
                }
            }
            getSelectedIds() {
                return this.selectedIds;
            }
            getIdentityStringById(id) {
                if (this.identityStrings[id] !== undefined) {
                    return this.identityStrings[id];
                }
                return null;
            }
            getSelectorById(id) {
                if (this.selectors[id] !== undefined) {
                    return this.selectors[id];
                }
                return null;
            }
            setSelectedIds(selectedIds) {
                this.selectedIds = selectedIds;
                var that = this;
                for (var id in this.selectors) {
                    this.selectors[id].selected = that.containsSelectedId(id);
                }
            }
        }
        Display.MultiEntrySelectorObserver = MultiEntrySelectorObserver;
        class SingleEntrySelectorObserver {
            constructor(originalId = null) {
                this.originalId = originalId;
                this.selectedId = null;
                this.identityStrings = {};
                this.selectors = {};
                this.checkJqs = {};
                this.onChanged = (selector) => {
                    let id = selector.entry.id;
                    this.checkJqs[id].prop("checked", selector.selected);
                    this.chSelect(selector.selected, id);
                };
                this.onDisposed = (entry) => {
                    delete this.selectors[entry.id];
                };
                this.onRemoved = (entry) => {
                    this.chSelect(false, entry.id);
                };
                this.selectedId = originalId;
            }
            destroy() {
                for (let id in this.selectors) {
                    this.checkJqs[id].remove();
                    let entry = this.selectors[id].entry;
                    entry.off(Display.Entry.EventType.DISPOSED, this.onDisposed);
                    entry.off(Display.Entry.EventType.REMOVED, this.onRemoved);
                }
                this.identityStrings = {};
                this.selectors = {};
            }
            observeEntrySelector(selector) {
                let entry = selector.entry;
                let id = entry.id;
                if (this.selectors[id])
                    return;
                let checkJq = $("<input />", { "type": "radio" });
                selector.jQuery.empty();
                selector.jQuery.append(checkJq);
                checkJq.change(() => {
                    selector.selected = checkJq.is(":checked");
                });
                selector.onChanged(this.onChanged);
                selector.selected = this.selectedId === id;
                this.checkJqs[id] = checkJq;
                this.selectors[id] = selector;
                this.identityStrings[id] = entry.identityString;
                entry.on(Display.Entry.EventType.DISPOSED, this.onDisposed);
                entry.on(Display.Entry.EventType.REMOVED, this.onRemoved);
            }
            getSelectedIds() {
                return [this.selectedId];
            }
            chSelect(selected, id) {
                if (!selected) {
                    if (this.selectedId === id) {
                        this.selectedId = null;
                    }
                    return;
                }
                if (this.selectedId === id)
                    return;
                this.selectedId = id;
                for (let id in this.selectors) {
                    if (id === this.selectedId)
                        continue;
                    this.selectors[id].selected = false;
                }
            }
            getIdentityStringById(id) {
                if (this.identityStrings[id] !== undefined) {
                    return this.identityStrings[id];
                }
                return null;
            }
            getSelectorById(id) {
                if (this.selectors[id] !== undefined) {
                    return this.selectors[id];
                }
                return null;
            }
            setSelectedId(selectedId) {
                if (this.selectors[selectedId]) {
                    this.selectors[selectedId].selected = true;
                    return;
                }
                this.selectedId = selectedId;
                for (let id in this.selectors) {
                    this.selectors[id].selected = false;
                }
            }
        }
        Display.SingleEntrySelectorObserver = SingleEntrySelectorObserver;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        let Severity;
        (function (Severity) {
            Severity["PRIMARY"] = "primary";
            Severity["SECONDARY"] = "secondary";
            Severity["SUCCESS"] = "success";
            Severity["DANGER"] = "danger";
            Severity["INFO"] = "info";
            Severity["WARNING"] = "warning";
        })(Severity = Display.Severity || (Display.Severity = {}));
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Toggler {
            constructor(buttonJq, menuJq, mouseLeaveMs) {
                this.buttonJq = buttonJq;
                this.menuJq = menuJq;
                this.mouseLeaveMs = mouseLeaveMs;
                menuJq.hide();
            }
            toggle(e) {
                if (this.closeCallback) {
                    this.closeCallback(e);
                    return;
                }
                this.open();
            }
            close() {
                if (!this.closeCallback)
                    return;
                this.closeCallback();
            }
            open() {
                if (this.closeCallback)
                    return;
                this.menuJq.show();
                this.buttonJq.addClass("active");
                let bodyJq = $("body");
                let events = [];
                this.closeCallback = (e) => {
                    if (e && e.type == "click" && this.menuJq.has(e.target).length > 0) {
                        return;
                    }
                    for (let event of events) {
                        event.off();
                    }
                    this.closeCallback = null;
                    this.menuJq.hide();
                    this.buttonJq.removeClass("active");
                };
                events.push(new TogglerEvent(bodyJq, "click", this.closeCallback));
                if (this.mouseLeaveMs !== null) {
                    let delayTimer = new Timer(this.closeCallback, this.mouseLeaveMs);
                    delayTimer.start();
                    events.push(new TogglerEvent(bodyJq, "click", () => {
                        delayTimer.reset();
                    }));
                    events.push(new TogglerEvent(this.menuJq, "mouseleave", () => {
                        delayTimer.start();
                    }));
                    events.push(new TogglerEvent(this.menuJq, "mouseenter", () => {
                        delayTimer.reset();
                    }));
                }
                for (let event of events) {
                    event.on();
                }
            }
            static simple(buttonJq, menuJq, mouseLeaveMs = 3000) {
                let toggler = new Toggler(buttonJq, menuJq, mouseLeaveMs);
                buttonJq.on("click", (e) => {
                    e.stopImmediatePropagation();
                    toggler.toggle(e);
                });
                return toggler;
            }
        }
        Display.Toggler = Toggler;
        class Timer {
            constructor(callback, delay) {
                this.callback = callback;
                this.delay = delay;
                this.timerId = null;
            }
            start() {
                if (this.started) {
                    this.reset();
                }
                this.timerId = window.setTimeout(this.callback, this.delay);
            }
            get started() {
                return this.timerId != null;
            }
            reset() {
                if (!this.started)
                    return;
                window.clearTimeout(this.timerId);
                this.timerId = null;
            }
        }
        Display.Timer = Timer;
        class TogglerEvent {
            constructor(elemJq, eventName, callback) {
                this.elemJq = elemJq;
                this.eventName = eventName;
                this.callback = callback;
            }
            on() {
                this.elemJq.on(this.eventName, this.callback);
            }
            off() {
                this.elemJq.off(this.eventName, this.callback);
            }
        }
        Display.TogglerEvent = TogglerEvent;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Confirm {
            constructor(msg, okLabel, cancelLabel, severity) {
                this.stressWindow = null;
                this.dialog = new Display.Dialog(msg, severity);
                this.dialog.addButton({ label: okLabel, type: "primary", callback: () => {
                        this.close();
                        if (this.successCallback) {
                            this.successCallback();
                        }
                    } });
                this.dialog.addButton({ label: cancelLabel, type: "secondary", callback: () => {
                        this.close();
                        if (this.cancelCallback) {
                            this.cancelCallback();
                        }
                    } });
            }
            open() {
                this.stressWindow = new Display.StressWindow();
                this.stressWindow.open(this.dialog);
            }
            close() {
                if (!this.stressWindow)
                    return;
                this.stressWindow.close();
                this.stressWindow = null;
            }
            static test(elemJq, successCallback) {
                if (!elemJq.data("rocket-confirm-msg"))
                    return null;
                return Confirm.fromElem(elemJq, successCallback);
            }
            static fromElem(elemJq, successCallback) {
                let confirm = new Confirm(elemJq.data("rocket-confirm-msg") || "Are you sure?", elemJq.data("rocket-confirm-ok-label") || "Yes", elemJq.data("rocket-confirm-cancel-label") || "No", "danger");
                confirm.successCallback = successCallback;
                return confirm;
            }
        }
        Display.Confirm = Confirm;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Dialog {
            constructor(msg, severity = "warning") {
                this.msg = msg;
                this._buttons = [];
                this.msg = msg;
                this.severity = severity;
            }
            addButton(button) {
                this.buttons.push(button);
            }
            get serverity() {
                return this.severity;
            }
            get buttons() {
                return this._buttons;
            }
        }
        Display.Dialog = Dialog;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class StressWindow {
            constructor() {
                this.elemBackgroundJq = $("<div />", {
                    "class": "rocket-dialog-background"
                }).css({
                    "position": "fixed",
                    "height": "100%",
                    "width": "100%",
                    "top": 0,
                    "left": 0,
                    "z-index": 998,
                    "opacity": 0
                });
                this.elemDialogJq = $("<div />").css({
                    "position": "fixed",
                    "z-index": 999
                });
                this.elemMessageJq = $("<p />", {
                    "class": "rocket-dialog-message"
                }).appendTo(this.elemDialogJq);
                this.elemControlsJq = $("<div/>", {
                    "class": "rocket-dialog-controls"
                }).appendTo(this.elemDialogJq);
            }
            open(dialog) {
                var that = this, elemBody = $("body"), elemWindow = $(window);
                this.elemDialogJq.removeClass()
                    .addClass("rocket-dialog-" + dialog.serverity + " rocket-dialog");
                this.elemMessageJq.empty().text(dialog.msg);
                this.initButtons(dialog);
                elemBody.append(this.elemBackgroundJq).append(this.elemDialogJq);
                this.elemDialogJq.css({
                    "left": (elemWindow.width() - this.elemDialogJq.outerWidth(true)) / 2,
                    "top": (elemWindow.height() - this.elemDialogJq.outerHeight(true)) / 3
                }).hide();
                this.elemBackgroundJq.show().animate({
                    opacity: 0.7
                }, 151, function () {
                    that.elemDialogJq.show();
                });
                elemWindow.on('keydown.dialog', function (event) {
                    var keyCode = (window.event) ? event.keyCode : event.which;
                    if (keyCode == 13) {
                        that.elemConfirmJq.click();
                        $(window).off('keydown.dialog');
                    }
                    else if (keyCode == 27) {
                        that.close();
                    }
                });
            }
            initButtons(dialog) {
                var that = this;
                this.elemConfirmJq = null;
                this.elemControlsJq.empty();
                dialog.buttons.forEach((button) => {
                    var elemA = $("<a>", {
                        "href": "#"
                    }).addClass("btn btn-" + button.type).click((e) => {
                        e.preventDefault();
                        button.callback(e);
                        that.close();
                    }).text(button.label);
                    if (that.elemConfirmJq == null) {
                        that.elemConfirmJq = elemA;
                    }
                    that.elemControlsJq.append(elemA);
                    that.elemControlsJq.append(" ");
                });
            }
            removeCurrentFocus() {
                $("<input/>", {
                    "type": "text",
                    "name": "remove-focus"
                }).appendTo($("body")).focus().remove();
            }
            close() {
                this.elemBackgroundJq.detach();
                this.elemDialogJq.detach();
                $(window).off('keydown.dialog');
            }
            ;
        }
        Display.StressWindow = StressWindow;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class Nav {
            init(elemJq) {
                this.elemJq = elemJq;
            }
            scrollToPos(scrollPos) {
                this.elemJq.animate({
                    scrollTop: scrollPos
                }, 0);
            }
            get state() {
                return this._state;
            }
            set state(value) {
                this._state = value;
            }
            get elemJq() {
                return this._elemJq;
            }
            set elemJq(value) {
                this._elemJq = value;
            }
        }
        Display.Nav = Nav;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class NavGroup {
            constructor(id, elemJq, userState) {
                this.id = id;
                this.elemJq = elemJq;
                this.userState = userState;
                this.opened = userState.isGroupOpen(id);
                if (this.opened) {
                    this.open(0);
                }
                else {
                    this.close(0);
                }
            }
            static build(elemJq, userStore) {
                let id = elemJq.data("navGroupId");
                let navGroup = new NavGroup(id, elemJq, userStore.navState);
                userStore.navState.onChanged(elemJq, navGroup);
                elemJq.find("h3").on("click", () => {
                    navGroup.toggle();
                    userStore.save();
                });
                return navGroup;
            }
            toggle() {
                if (this.opened) {
                    this.close(150);
                }
                else {
                    this.open(150);
                }
            }
            changed() {
                if (this.userState.isGroupOpen(this.id) === this.opened)
                    return;
                this.opened = this.userState.isGroupOpen(this.id);
                if (this.opened === true) {
                    this.open();
                }
                if (this.opened === false) {
                    this.close();
                }
            }
            open(ms = 150) {
                this.opened = true;
                let icon = this.elemJq.find("h3").find("i");
                icon.addClass("fa-minus");
                icon.removeClass("fa-plus");
                this.elemJq.find('.nav').stop(true, true).slideDown({ duration: ms });
                this.userState.change(this.id, this.opened);
            }
            close(ms = 150) {
                this.opened = false;
                let icon = this.elemJq.find("h3").find("i");
                icon.addClass("fa-plus");
                icon.removeClass("fa-minus");
                this.elemJq.find('.nav').stop(true, true).slideUp({ duration: ms });
                this.userState.change(this.id, this.opened);
            }
            get userState() {
                return this._userState;
            }
            set userState(value) {
                this._userState = value;
            }
            get elemJq() {
                return this._elemJq;
            }
            set elemJq(value) {
                this._elemJq = value;
            }
            get id() {
                return this._id;
            }
            set id(value) {
                this._id = value;
            }
            get opened() {
                return this._opened;
            }
            set opened(value) {
                this._opened = value;
            }
        }
        Display.NavGroup = NavGroup;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Display;
    (function (Display) {
        class NavItem {
            constructor(htmlElement) {
                this._htmlElement = htmlElement;
            }
            get htmlElement() {
                return this._htmlElement;
            }
            set htmlElement(value) {
                this._htmlElement = value;
            }
        }
        Display.NavItem = NavItem;
    })(Display = Rocket.Display || (Rocket.Display = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var $ = jQuery;
        class Form {
            constructor(jqForm) {
                this._config = new Form.Config();
                this.jqForm = jqForm;
                this._jForm = Jhtml.Ui.Form.from(jqForm.get(0));
                this._jForm.on("submit", () => {
                    this.block();
                });
                this._jForm.on("submitted", () => {
                    this.unblock();
                });
            }
            get jQuery() {
                return this.jqForm;
            }
            get jForm() {
                return this._jForm;
            }
            get config() {
                return this._config;
            }
            block() {
                let zone;
                if (!this.lock && this.config.blockPage && (zone = Rocket.Cmd.Zone.of(this.jqForm))) {
                    this.lock = zone.createLock();
                }
            }
            unblock() {
                if (this.lock) {
                    this.lock.release();
                    this.lock = null;
                }
            }
            static from(jqForm) {
                var form = jqForm.data("rocketImplForm");
                if (form instanceof Form)
                    return form;
                if (jqForm.length == 0) {
                    throw new Error("Invalid argument");
                }
                form = new Form(jqForm);
                jqForm.data("rocketImplForm", form);
                return form;
            }
        }
        Impl.Form = Form;
        (function (Form) {
            class Config {
                constructor() {
                    this.blockPage = true;
                }
            }
            Form.Config = Config;
            let EventType;
            (function (EventType) {
                EventType[EventType["SUBMIT"] = 0] = "SUBMIT";
                EventType[EventType["SUBMITTED"] = 1] = "SUBMITTED";
            })(EventType = Form.EventType || (Form.EventType = {}));
        })(Form = Impl.Form || (Impl.Form = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var File;
        (function (File) {
            class SelectableDimension {
                constructor(resizer, elemLi) {
                    this.resizer = resizer;
                    this.elemLi = elemLi;
                    this.resizer.getSizeSelector().registerChangeListener(this);
                    this.elemLowRes = elemLi.find(".rocket-image-low-res").hide();
                    this.elemRadio = elemLi.find("input[type=radio]:first");
                    this.elemThumb = elemLi.find(".rocket-image-previewable:first");
                    this.dimensionStr = this.elemRadio.data('dimension-str').toString();
                    this.ratioStr = elemLi.data('ratio-str').toString();
                    this.ratio = this.ratioStr === this.elemRadio.val();
                    (function (that) {
                        if (that.elemRadio.is(":checked")) {
                            that.resizer.setSelectedDimension(that, false);
                        }
                        that.elemRadio.change(function () {
                            if (that.elemRadio.is(":checked")) {
                                that.resizer.setSelectedDimension(that, true);
                            }
                        });
                        if (!this.ratio) {
                            if (that.elemThumb.length > 0) {
                                this.elemLi.append($("<a />", {
                                    "href": "#"
                                }).click(function (e) {
                                    e.preventDefault();
                                    that.elemThumb.click();
                                    that.elemLi.nextUntil(".rocket-image-ratio");
                                }).append($("<i />", {
                                    "class": "fa fa-search"
                                })));
                            }
                        }
                        else {
                            var elemToggleOpen = $("<i />", {
                                "class": "fa fa-chevron-down"
                            });
                            var elemToggleClose = $("<i />", {
                                "class": "fa fa-chevron-up"
                            });
                            let elemsToToggle = that.elemLi.siblings("[data-ratio-str=" + that.ratioStr + "]"), elemA = $("<a />", {
                                "href": "#",
                                "class": "open btn btn-secondary"
                            }).click(function (e) {
                                e.preventDefault();
                                var elem = $(this);
                                if (elem.hasClass("open")) {
                                    elemsToToggle.hide();
                                    elem.removeClass("open");
                                    elemToggleOpen.show();
                                    elemToggleClose.hide();
                                    that.setOpen(false);
                                }
                                else {
                                    elemsToToggle.show();
                                    elem.addClass("open");
                                    elemToggleOpen.hide();
                                    elemToggleClose.show();
                                    that.setOpen(true);
                                }
                            }).append(elemToggleOpen).append(elemToggleClose).appendTo($("<div />", {
                                "class": "rocket-simple-commands"
                            }).appendTo(this.elemLi));
                            if (!that.checkOpen() && elemsToToggle.find("input[type=radio]:checked").length === 0) {
                                elemA.click();
                            }
                            else {
                                elemToggleOpen.hide();
                            }
                        }
                    }).call(this, this);
                }
                checkOpen() {
                    if (typeof (Storage) === "undefined")
                        return false;
                    let item;
                    if (null !== (item = sessionStorage.getItem(this.buildStorageKey() + "-open"))) {
                        return JSON.parse(item);
                    }
                    return false;
                }
                setOpen(open) {
                    if (typeof (Storage) === "undefined")
                        return;
                    sessionStorage.setItem(this.buildStorageKey() + "-open", JSON.stringify(open));
                }
                getDimensionStr() {
                    return this.dimensionStr;
                }
                getRatioStr() {
                    return this.ratioStr;
                }
                isRatio() {
                    return this.ratio;
                }
                select() {
                    this.elemRadio.prop("checked", true);
                }
                buildStorageKey() {
                    return location.href + '/' + this.ratioStr;
                }
                hasSameRatio(selectableDimension) {
                    return selectableDimension.getRatioStr() === this.ratioStr;
                }
                equals(selectableDimension) {
                    return this.hasSameRatio(selectableDimension) && selectableDimension.getDimensionStr() === this.dimensionStr
                        && this.isRatio() === selectableDimension.isRatio();
                }
                onDimensionChange(sizeSelector) {
                    this.checkLowRes(sizeSelector);
                }
                onDimensionChanged(sizeSelector) {
                    this.checkLowRes(sizeSelector);
                }
                checkLowRes(sizeSelector) {
                    let currentResizingDimension = sizeSelector.getCurrentResizingDimension();
                    if (null === currentResizingDimension)
                        return;
                    let currentSelectableDimension = currentResizingDimension.getSelectableDimension();
                    if (null === currentSelectableDimension)
                        return;
                    if (((currentSelectableDimension.isRatio() && currentSelectableDimension.hasSameRatio(this))
                        || currentSelectableDimension.equals(this)) && sizeSelector.isLowRes(this.createResizingDimension())) {
                        this.elemLowRes.show();
                    }
                    else {
                        this.elemLowRes.hide();
                    }
                }
                createResizingDimension() {
                    return new ResizingDimension(this, this.resizer.getZoomFactor());
                }
            }
            class ResizingDimension {
                constructor(selectableDimension, zoomFactor) {
                    this.selectableDimension = selectableDimension;
                    this.zoomFactor = zoomFactor;
                    this.ratio = 1;
                    this.initialize();
                }
                initialize() {
                    var dimensionStr = this.selectableDimension.getDimensionStr();
                    if (dimensionStr.match(ResizingDimension.dimensionMatchPattern) === null)
                        return;
                    let dimension = dimensionStr.split("x");
                    this.width = parseInt(dimension[0]) * this.zoomFactor;
                    this.height = parseInt(dimension[1]) * this.zoomFactor;
                    if (dimension.length <= 2) {
                        this.crop = false;
                    }
                    else {
                        this.crop = dimension[2].startsWith("c");
                    }
                    this.ratio = this.width / this.height;
                }
                getSelectableDimension() {
                    return this.selectableDimension;
                }
                isCrop() {
                    return this.crop;
                }
                getRatio() {
                    return this.ratio;
                }
                getWidth() {
                    return this.width;
                }
                getHeight() {
                    return this.height;
                }
                buildStorageKey() {
                    return this.getSelectableDimension().buildStorageKey();
                }
            }
            ResizingDimension.dimensionMatchPattern = new RegExp("\\d+x\\d+[xcrop]?");
            class Dimension {
                constructor(width, height) {
                    this.width = width;
                    this.height = height;
                }
            }
            class DragStart {
                constructor() {
                    this.positionTop = null;
                    this.positionLeft = null;
                    this.mouseOffsetTop = null;
                    this.mouseOffsetLeft = null;
                }
            }
            class ResizeStart {
                constructor() {
                    this.width = null;
                    this.height = null;
                    this.mouseOffsetTop = null;
                    this.mouseOffsetLeft = null;
                }
            }
            class SizeSelector {
                constructor(imageResizer, elemImg) {
                    this.imageResizer = imageResizer;
                    this.elemImg = elemImg;
                    this.fixedRatio = false;
                    this.currentResizingDimension = null;
                    this.elemDiv = null;
                    this.elemSpan = null;
                    this.imageLoaded = false;
                    this.dragStart = null;
                    this.resizeStart = null;
                    this.max = null;
                    this.min = null;
                    this.changeListeners = [];
                    this.initialize();
                }
                getCurrentResizingDimension() {
                    return this.currentResizingDimension;
                }
                getPositionTop() {
                    return this.elemDiv.position().top;
                }
                getPositionLeft() {
                    return this.elemDiv.position().left;
                }
                getWidth() {
                    return this.elemDiv.width();
                }
                getHeight() {
                    return this.elemDiv.height();
                }
                setFixedRatio(fixedRatio) {
                    this.fixedRatio = fixedRatio;
                    this.checkRatio();
                }
                initialize() {
                    this.initializeResizeStart();
                    this.initializeDragStart();
                }
                checkRatio() {
                    if (!this.fixedRatio || !this.currentResizingDimension)
                        return;
                    var width = this.elemDiv.width();
                    var height = this.elemDiv.height();
                    if (width < height) {
                        this.elemDiv.height(width / this.currentResizingDimension.getRatio());
                    }
                    else {
                        this.elemDiv.width(height * this.currentResizingDimension.getRatio());
                    }
                    this.elemDiv.trigger('sizeChange');
                }
                initializeMin() {
                    let spanHeight = this.elemSpan.height();
                    if (this.fixedRatio && null !== this.currentResizingDimension) {
                        var ratio = this.currentResizingDimension.getRatio();
                        if (this.currentResizingDimension.getWidth() > this.currentResizingDimension.getHeight()) {
                            this.min = new Dimension(spanHeight * ratio, spanHeight);
                        }
                        else {
                            this.min = new Dimension(spanHeight, spanHeight / ratio);
                        }
                    }
                    else {
                        this.min = new Dimension(this.elemSpan.width(), this.elemSpan.height());
                    }
                }
                initializeMax() {
                    let imageWidth = this.elemImg.width(), imageHeight = this.elemImg.height(), dimensionWidth, dimensionHeight;
                    if (this.fixedRatio && null !== this.currentResizingDimension) {
                        var ratio = this.currentResizingDimension.getRatio();
                        dimensionWidth = imageHeight * ratio;
                        if (dimensionWidth > imageWidth) {
                            dimensionWidth = imageWidth;
                        }
                        dimensionHeight = imageWidth / ratio;
                        if (dimensionHeight > imageHeight) {
                            dimensionHeight = imageHeight;
                        }
                    }
                    else {
                        dimensionWidth = imageWidth;
                        dimensionHeight = imageHeight;
                    }
                    this.max = new Dimension(dimensionWidth, dimensionHeight);
                    this.max.top = 0;
                    this.max.left = 0;
                    this.max.right = imageWidth;
                    this.max.bottom = imageHeight;
                }
                initializeDragStart() {
                    this.dragStart = new DragStart();
                }
                initializeResizeStart() {
                    this.resizeStart = new ResizeStart();
                }
                checkPositionRight(newRight) {
                    return this.max.right > newRight;
                }
                checkPositionLeft(newLeft) {
                    return this.max.left < newLeft;
                }
                checkPositionBottom(newBottom) {
                    return this.max.bottom > newBottom;
                }
                checkPositionTop(newTop) {
                    return this.max.top < newTop;
                }
                checkPositions(newTop, newRight, newBottom, newLeft) {
                    return this.checkPositionTop(newTop) && this.checkPositionRight(newRight)
                        && this.checkPositionBottom(newBottom) && this.checkPositionLeft(newLeft);
                }
                isLowRes(resizingDimension = null) {
                    if (!resizingDimension) {
                        resizingDimension = this.currentResizingDimension;
                    }
                    if (!resizingDimension)
                        return false;
                    return resizingDimension.getWidth() > (this.getWidth() + 1)
                        || resizingDimension.getHeight() > (this.getHeight() + 1);
                }
                initializeUI() {
                    var _obj = this;
                    if (!this.imageLoaded) {
                        this.elemDiv = $("<div/>").css({
                            zIndex: 40,
                            position: "absolute",
                            overflow: "hidden"
                        }).addClass("rocket-image-resizer-size-selector");
                        this.elemImg = $("<img/>").css("position", "relative");
                        this.elemImg.on("load", () => {
                            this.imageLoaded = true;
                            this.initializeUI();
                            window.scroll(0, Jhtml.Monitor.of(this.elemImg.get(0)).history.currentPage.config.scrollPos);
                        }).attr("src", this.imageResizer.getElemImg().attr("src"));
                        this.elemDiv.append(this.elemImg);
                        this.imageResizer.getElemContent().append(this.elemDiv);
                        this.elemSpan = $("<span/>").css({
                            zIndex: 41,
                            position: "absolute",
                            right: "-1px",
                            bottom: "-1px"
                        });
                    }
                    else {
                        this.imageResizer.getElemContent().css({
                            position: "relative"
                        });
                        this.elemImg.width(this.imageResizer.getElemImg().width()).height(this.imageResizer.getElemImg().height());
                        this.elemDiv.mousedown(function (event) {
                            _obj.dragStart.positionTop = _obj.elemDiv.position().top;
                            _obj.dragStart.positionLeft = _obj.elemDiv.position().left;
                            _obj.dragStart.mouseOffsetTop = event.pageY;
                            _obj.dragStart.mouseOffsetLeft = event.pageX;
                            $(document).on('mousemove.drag', function (event) {
                                var newTop = _obj.dragStart.positionTop - (_obj.dragStart.mouseOffsetTop - event.pageY);
                                var newLeft = _obj.dragStart.positionLeft - (_obj.dragStart.mouseOffsetLeft - event.pageX);
                                var newRight = newLeft + _obj.elemDiv.width();
                                var newBottom = newTop + _obj.elemDiv.height();
                                if (!_obj.checkPositions(newTop, newRight, newBottom, newLeft)) {
                                    !_obj.checkPositionTop(newTop) && (newTop = _obj.max.top);
                                    !_obj.checkPositionLeft(newLeft) && (newLeft = _obj.max.left);
                                    !_obj.checkPositionRight(newRight) && (newLeft = _obj.max.right - _obj.elemDiv.width());
                                    !_obj.checkPositionBottom(newBottom) && (newTop = _obj.max.bottom - _obj.elemDiv.height());
                                }
                                _obj.elemDiv.css({
                                    top: newTop + "px",
                                    left: newLeft + "px"
                                }).trigger('positionChange');
                                $.Event(event).preventDefault();
                            }).on('mouseup.drag', function (event) {
                                $(document).off("mousemove.drag");
                                $(document).off("mouseup.drag");
                                _obj.initializeDragStart();
                                _obj.triggerDimensionChanged();
                                $.Event(event).preventDefault();
                            });
                            $.Event(event).preventDefault();
                            $.Event(event).stopPropagation();
                        }).on('positionChange', function () {
                            _obj.elemImg.css({
                                top: (-1 * $(this).position().top) + "px",
                                left: (-1 * $(this).position().left) + "px"
                            });
                        }).on('sizeChange', function () {
                            if (_obj.isLowRes()) {
                                _obj.showWarning();
                            }
                            else {
                                _obj.hideWarning();
                            }
                            _obj.triggerDimensionChange();
                        });
                        this.elemSpan.mousedown(function (event) {
                            _obj.resizeStart.width = _obj.elemDiv.width();
                            _obj.resizeStart.height = _obj.elemDiv.height();
                            _obj.resizeStart.mouseOffsetTop = event.pageY;
                            _obj.resizeStart.mouseOffsetLeft = event.pageX;
                            $(document).on('mousemove.resize', function (event) {
                                var newWidth = _obj.resizeStart.width - (_obj.resizeStart.mouseOffsetLeft - event.pageX);
                                var newHeight = _obj.resizeStart.height - (_obj.resizeStart.mouseOffsetTop - event.pageY);
                                console.log(_obj.fixedRatio);
                                if (_obj.fixedRatio) {
                                    var heightProportion = newHeight / _obj.resizeStart.height;
                                    var widthProportion = newWidth / _obj.resizeStart.width;
                                    if (widthProportion >= heightProportion) {
                                        newHeight = _obj.resizeStart.height * widthProportion;
                                    }
                                    else {
                                        newWidth = _obj.resizeStart.width * heightProportion;
                                    }
                                }
                                var newRight = _obj.getPositionLeft() + newWidth;
                                var newBottom = _obj.getPositionTop() + newHeight;
                                if ((!_obj.checkPositionRight(newRight)) || (!_obj.checkPositionBottom(newBottom))) {
                                    if (!_obj.checkPositionRight(newRight)) {
                                        newWidth = _obj.elemImg.width() - _obj.getPositionLeft();
                                        if (_obj.fixedRatio && _obj.checkPositionBottom(newBottom)) {
                                            newHeight = _obj.resizeStart.height * newWidth / _obj.resizeStart.width;
                                        }
                                    }
                                    if (!_obj.checkPositionBottom(newBottom)) {
                                        newHeight = _obj.elemImg.height() - _obj.getPositionTop();
                                        if (_obj.fixedRatio && _obj.checkPositionRight(newRight)) {
                                            newWidth = _obj.resizeStart.width * newHeight / _obj.resizeStart.height;
                                        }
                                    }
                                }
                                _obj.setSelectorDimensions(newWidth, newHeight);
                                event.preventDefault();
                            }).on('mouseup.resize', function (event) {
                                $(document).off("mousemove.resize");
                                $(document).off("mouseup.resize");
                                _obj.initializeResizeStart();
                                _obj.triggerDimensionChanged();
                            });
                            event.preventDefault();
                            event.stopPropagation();
                        });
                        this.elemDiv.append(this.elemSpan);
                        this.initializeMax();
                        this.initializeMin();
                        this.setSelectorDimensions(this.elemDiv.width(), this.elemDiv.height());
                        this.redraw(this.imageResizer.getSelectedDimension().createResizingDimension());
                    }
                }
                setSelectorDimensions(newWidth, newHeight) {
                    if (this.min.width > newWidth) {
                        newWidth = this.min.width;
                    }
                    if (this.min.height > newHeight) {
                        newHeight = this.min.height;
                    }
                    if (this.max.width < newWidth) {
                        newWidth = this.max.width;
                    }
                    if (this.max.height < newHeight) {
                        newHeight = this.max.height;
                    }
                    this.elemDiv.width(newWidth).height(newHeight);
                    this.elemDiv.trigger('sizeChange');
                }
                updateImage() {
                    this.elemImg.width(this.imageResizer.getElemImg().width());
                    this.elemImg.height(this.imageResizer.getElemImg().height());
                    this.initializeMax();
                    this.initializeMin();
                }
                registerChangeListener(changeListener) {
                    this.changeListeners.push(changeListener);
                }
                triggerDimensionChange() {
                    this.changeListeners.forEach(function (chnageListener) {
                        chnageListener.onDimensionChange(this);
                    }, this);
                }
                triggerDimensionChanged() {
                    this.changeListeners.forEach(function (chnageListener) {
                        chnageListener.onDimensionChanged(this);
                    }, this);
                }
                redraw(resizingDimension) {
                    var dimensions = this.imageResizer.determineCurrentDimensions(resizingDimension);
                    this.elemDiv.css({
                        top: dimensions.top + "px",
                        left: dimensions.left + "px"
                    }).width(dimensions.width).height(dimensions.height);
                    this.currentResizingDimension = resizingDimension;
                    this.elemDiv.trigger('positionChange');
                    this.elemDiv.trigger('sizeChange');
                    this.triggerDimensionChanged();
                    this.initializeMin();
                    this.initializeMax();
                }
                showWarning() {
                    this.imageResizer.showLowResolutionWarning();
                }
                hideWarning() {
                    this.imageResizer.hideResolutionWarning();
                }
            }
            class Resizer {
                constructor(elem, elemDimensionContainer, elemImg = null, maxHeightCheckClosure = null) {
                    this.elem = elem;
                    this.elemDimensionContainer = elemDimensionContainer;
                    this.elemImg = elemImg;
                    this.maxHeightCheckClosure = maxHeightCheckClosure;
                    this.elemContent = null;
                    this.elemLowResolutionContainer = null;
                    this.elemFixedRatioContainer = null;
                    this.elemCbxFixedRatio = null;
                    this.elemSpanZoom = $("<span />");
                    this.dimensions = [];
                    this.zoomFactor = 1;
                    this.lastWidth = null;
                    this.originalImageWidth = null;
                    this.originalImageHeight = null;
                    if (null === this.elemImg) {
                        this.elemImg = $("<img/>").attr("src", elem.attr("data-img-src"));
                    }
                    this.textFixedRatio = elem.data("text-fixed-ratio") || "Fixed Ratio";
                    this.textLowResolution = elem.data("text-low-resolution") || "Low Resolution";
                    this.textZoom = elem.data("text-zoom") || "Zoom";
                    this.sizeSelector = new SizeSelector(this, this.elemImg);
                    let firstSelectableDimension = null, _obj = this;
                    this.elemDimensionContainer.find(".rocket-image-version").each(function () {
                        var selectableDimension = new SelectableDimension(_obj, $(this));
                        if (null === firstSelectableDimension) {
                            firstSelectableDimension = selectableDimension;
                        }
                    });
                    if (!this.selectedDimension && firstSelectableDimension) {
                        firstSelectableDimension.select();
                        this.setSelectedDimension(firstSelectableDimension, false);
                    }
                    this.sizeSelector.registerChangeListener(this);
                    this.initializeUi();
                }
                getSelectedDimension() {
                    return this.selectedDimension;
                }
                setSelectedDimension(selectedDimension, redraw) {
                    this.selectedDimension = selectedDimension;
                    if (redraw) {
                        let resizingDimension = selectedDimension.createResizingDimension();
                        this.checkFixedRatio(resizingDimension);
                        this.sizeSelector.redraw(resizingDimension);
                    }
                }
                getElemContent() {
                    return this.elemContent;
                }
                getElemImg() {
                    return this.elemImg;
                }
                getSizeSelector() {
                    return this.sizeSelector;
                }
                getZoomFactor() {
                    return this.zoomFactor;
                }
                initializeUi() {
                    this.initLowResolutionContainer();
                    this.elemContent = $("<div/>")
                        .addClass("rocket-image-resizer-content")
                        .append($("<div/>").addClass("rocket-image-resizer-content-overlay"));
                    this.elemContent.append(this.elemImg).appendTo(this.elem);
                    this.initFixedRatioContainer();
                    var _obj = this;
                    this.elemImg.on("load", function () {
                        _obj.originalImageWidth = $(this).width();
                        _obj.originalImageHeight = $(this).height();
                        _obj.applyZoomFactor();
                        _obj.elemImg.width(_obj.originalImageWidth * _obj.zoomFactor);
                        _obj.elemImg.height(_obj.originalImageHeight * _obj.zoomFactor);
                        _obj.initializeUIChildContainers();
                        _obj.elem.on('containerWidthChange', function () {
                            _obj.applyZoomFactor();
                            _obj.elemImg.width(_obj.originalImageWidth * _obj.zoomFactor);
                            _obj.elemImg.height(_obj.originalImageHeight * _obj.zoomFactor);
                            _obj.sizeSelector.updateImage();
                            _obj.sizeSelector.redraw(_obj.selectedDimension.createResizingDimension());
                        });
                    });
                }
                applyZoomFactor() {
                    let _obj = this, accuracy = 100000, zoomFactorHeight = 1, zoomFactorWidth;
                    if (this.maxHeightCheckClosure !== null) {
                        zoomFactorHeight = (Math.ceil(this.maxHeightCheckClosure() / this.originalImageHeight * accuracy) - 1) / accuracy;
                    }
                    zoomFactorWidth = (Math.ceil(_obj.elem.width() / this.originalImageWidth * accuracy) - 1) / accuracy;
                    if (zoomFactorHeight > zoomFactorWidth) {
                        this.zoomFactor = zoomFactorWidth;
                    }
                    else {
                        this.zoomFactor = zoomFactorHeight;
                    }
                    if (this.zoomFactor !== 1) {
                        this.elemSpanZoom.show().text(this.textZoom + ": " + (this.zoomFactor * 100).toFixed(0) + "%");
                    }
                    else {
                        this.elemSpanZoom.hide();
                    }
                }
                initializeUIChildContainers() {
                    let _obj = this;
                    this.sizeSelector.initializeUI();
                    this.checkFixedRatio(this.selectedDimension.createResizingDimension());
                    this.lastWidth = this.elem.width();
                    $(window).resize(function () {
                        if (_obj.lastWidth != _obj.elem.width()) {
                            _obj.lastWidth = _obj.elem.width();
                            _obj.elem.trigger('containerWidthChange');
                        }
                    });
                }
                onDimensionChange(sizeSelector) { }
                onDimensionChanged(sizeSelector) {
                    var _obj = this;
                    var width = sizeSelector.getWidth() / _obj.zoomFactor;
                    if (width > this.originalImageWidth) {
                        width = this.originalImageWidth;
                    }
                    var height = sizeSelector.getHeight() / _obj.zoomFactor;
                    if (height > this.originalImageHeight) {
                        height = this.originalImageHeight;
                    }
                    this.elem.trigger('dimensionChanged', [{
                            left: sizeSelector.getPositionLeft() / _obj.zoomFactor,
                            top: sizeSelector.getPositionTop() / _obj.zoomFactor,
                            width: width,
                            height: height
                        }]);
                    SizeSelectorPositions.addPositions(sizeSelector, _obj.zoomFactor);
                }
                initFixedRatioContainer() {
                    this.elemFixedRatioContainer = $("<div/>").addClass("rocket-fixed-ratio-container").appendTo(this.elem);
                    var randomId = "rocket-image-resizer-fixed-ratio-" + Math.floor((Math.random() * 10000)), that = this;
                    this.elemFixedRatioContainer.append($("<label/>", {
                        "for": randomId,
                        "text": this.textFixedRatio
                    }).css("display", "inline-block"));
                    this.elemCbxFixedRatio = $("<input type='checkbox'/>").addClass("rocket-image-resizer-fixed-ratio").attr("id", randomId)
                        .change(function () {
                        that.sizeSelector.setFixedRatio($(this).prop("checked"));
                        that.sizeSelector.initializeMin();
                        that.sizeSelector.initializeMax();
                    }).appendTo(this.elemFixedRatioContainer);
                }
                checkFixedRatio(resizingDimension) {
                    this.elemCbxFixedRatio.prop("checked", true);
                    if (resizingDimension.isCrop()) {
                        this.elemFixedRatioContainer.hide();
                    }
                    else {
                        this.elemFixedRatioContainer.show();
                    }
                    this.elemCbxFixedRatio.trigger("change");
                }
                initLowResolutionContainer() {
                    this.elemLowResolutionContainer = $("<div/>")
                        .addClass("rocket-low-resolution-container").appendTo(this.elem).hide();
                    $("<span />", {
                        "class": "rocket-image-resizer-warning",
                        "text": this.textLowResolution
                    }).appendTo(this.elemLowResolutionContainer);
                }
                showLowResolutionWarning() {
                    this.elemLowResolutionContainer.show();
                }
                hideResolutionWarning() {
                    this.elemLowResolutionContainer.hide();
                }
                determineCurrentDimensions(resizingDimension) {
                    var sizeSelectorPosition = SizeSelectorPositions.getPositions(resizingDimension, this.zoomFactor);
                    if (null !== sizeSelectorPosition)
                        return sizeSelectorPosition;
                    var top = 0, left = 0, width = resizingDimension.getWidth(), imageWidth = this.elemImg.width(), height = resizingDimension.getHeight(), imageHeight = this.elemImg.height(), widthExceeded = false, heightExceeded = false, ratio = resizingDimension.getRatio();
                    if (width > imageWidth) {
                        widthExceeded = true;
                        width = imageWidth;
                    }
                    else {
                        left = (imageWidth - width) / 2;
                    }
                    if (height > imageHeight) {
                        height = imageHeight;
                        heightExceeded = true;
                    }
                    else {
                        top = (imageHeight - height) / 2;
                    }
                    if (widthExceeded && heightExceeded) {
                        if ((width / height) > ratio) {
                            widthExceeded = false;
                        }
                        else {
                            heightExceeded = false;
                        }
                    }
                    if (widthExceeded) {
                        height = width / ratio;
                    }
                    else if (heightExceeded) {
                        width = height * ratio;
                    }
                    return new SizeSelectorPosition(left, top, width + 1, height + 1);
                }
            }
            class SizeSelectorPositions {
                static addPositions(sizeSelector, zoomFactor) {
                    var currentResizingDimension = sizeSelector.getCurrentResizingDimension();
                    if (typeof (Storage) === "undefined" || currentResizingDimension === null)
                        return;
                    let imageResizerPositions;
                    if (null == localStorage.imageResizer) {
                        imageResizerPositions = new Object();
                    }
                    else {
                        imageResizerPositions = JSON.parse(localStorage.imageResizer);
                    }
                    imageResizerPositions[currentResizingDimension.buildStorageKey()] = {
                        left: sizeSelector.getPositionLeft() / zoomFactor,
                        top: sizeSelector.getPositionTop() / zoomFactor,
                        width: sizeSelector.getWidth() / zoomFactor,
                        height: sizeSelector.getHeight() / zoomFactor
                    };
                    localStorage.imageResizer = JSON.stringify(imageResizerPositions);
                }
                static getPositions(resizingDimension, zoomFactor) {
                    if (typeof (Storage) === "undefined" || null == localStorage.imageResizer)
                        return null;
                    let imageResizerPositions = JSON.parse(localStorage.imageResizer);
                    if (!imageResizerPositions[resizingDimension.buildStorageKey()])
                        return null;
                    let jsonObj = imageResizerPositions[resizingDimension.buildStorageKey()];
                    return new SizeSelectorPosition(jsonObj['left'] * zoomFactor, jsonObj['top'] * zoomFactor, jsonObj['width'] * zoomFactor, jsonObj['height'] * zoomFactor);
                }
            }
            class SizeSelectorPosition {
                constructor(left, top, width, height) {
                    this.left = left;
                    this.top = top;
                    this.width = width;
                    this.height = height;
                }
            }
            class RocketResizer {
                constructor(elem) {
                    this.elem = elem;
                    let elemResizer = elem.find("#rocket-image-resizer"), elemPageControls = elem.find(".rocket-zone-commands:first"), elemRocketheader = $("#rocket-header"), elemWindow = $(window), elemDimensionContainer = elem.find(".rocket-image-dimensions:first");
                    this.resizer = new Resizer(elemResizer, elemDimensionContainer, null, function () {
                        var height = elemWindow.height() - 50;
                        if (elemRocketheader.length > 0) {
                            height -= elemRocketheader.outerHeight();
                        }
                        if (elemPageControls.length > 0) {
                            height -= elemPageControls.outerHeight();
                        }
                        return height;
                    });
                    let elemInpPositionX = elem.find("#rocket-thumb-pos-x").hide(), elemInpPositionY = elem.find("#rocket-thumb-pos-y").hide(), elemInpWidth = elem.find("#rocket-thumb-width").hide(), elemInpHeight = elem.find("#rocket-thumb-height").hide();
                    elem.find(".rocket-image-version > img").each(function () {
                        $(this).attr('src', $(this).attr("src") + "?timestamp=" + new Date().getTime());
                    });
                    elemResizer.on('dimensionChanged', function (event, dimension) {
                        elemInpPositionX.val(Math.floor(dimension.left));
                        elemInpPositionY.val(Math.floor(dimension.top));
                        elemInpWidth.val(Math.floor(dimension.width));
                        elemInpHeight.val(Math.floor(dimension.height));
                    });
                }
            }
            File.RocketResizer = RocketResizer;
        })(File = Impl.File || (Impl.File = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Order;
        (function (Order) {
            class Control {
                constructor(elemJq, insertMode, moveState, otherElemJq) {
                    this.elemJq = elemJq;
                    this.insertMode = insertMode;
                    this.moveState = moveState;
                    this.otherElemJq = otherElemJq;
                    this.entry = Rocket.Display.Entry.of(elemJq);
                    this.collection = this.entry.collection;
                    if (!this.collection || !this.entry.selector) {
                        this.elemJq.hide();
                        return;
                    }
                    if (!this.collection.selectable) {
                        this.collection.setupSelector(new Rocket.Display.MultiEntrySelectorObserver());
                    }
                    let onSelectionChanged = () => {
                        this.update();
                    };
                    this.collection.onSelectionChanged(onSelectionChanged);
                    this.entry.on(Rocket.Display.Entry.EventType.DISPOSED, () => {
                        this.collection.offSelectionChanged(onSelectionChanged);
                    });
                    this.update();
                    this.elemJq.click((evt) => {
                        evt.preventDefault();
                        this.exec();
                        return false;
                    });
                    this.setupSortable();
                }
                setupSortable() {
                    if (this.insertMode != InsertMode.AFTER && this.insertMode != InsertMode.BEFORE) {
                        return;
                    }
                    this.collection.setupSortable();
                    this.collection.onInsert((entries) => {
                        if (this.moveState.executing)
                            return;
                        this.prepare(entries);
                    });
                    this.collection.onInserted((entries, aboveEntry, belowEntry) => {
                        if (this.moveState.executing)
                            return;
                        if (this.insertMode == InsertMode.BEFORE) {
                            if (aboveEntry === null && this.entry === this.collection.entries[1]) {
                                this.dingsel(entries);
                                return;
                            }
                            if (belowEntry === this.entry && this.entry.treeLevel !== null
                                && aboveEntry.treeLevel < this.entry.treeLevel) {
                                this.dingsel(entries);
                                return;
                            }
                        }
                        if (this.insertMode == InsertMode.AFTER && this.entry === aboveEntry
                            && (belowEntry === null || this.entry.treeLevel === null || belowEntry.treeLevel <= this.entry.treeLevel)) {
                            this.dingsel(entries);
                            return;
                        }
                    });
                }
                get jQuery() {
                    return this.elemJq;
                }
                prepare(entries) {
                    this.moveState.memorizeTreeDecendants(entries);
                }
                update() {
                    if ((this.entry.selector && this.entry.selector.selected)
                        || this.collection.selectedIds.length == 0
                        || this.checkIfParentSelected()) {
                        this.elemJq.hide();
                        this.otherElemJq.show();
                    }
                    else {
                        this.elemJq.show();
                        this.otherElemJq.hide();
                    }
                }
                checkIfParentSelected() {
                    if (this.entry.treeLevel === null)
                        return false;
                    return !!this.entry.collection.findTreeParents(this.entry)
                        .find((parentEntry) => {
                        return parentEntry.selector && parentEntry.selector.selected;
                    });
                }
                exec() {
                    this.moveState.executing = true;
                    let entries = this.collection.selectedEntries;
                    this.prepare(entries);
                    if (this.insertMode == InsertMode.BEFORE) {
                        this.collection.insertAfter(this.collection.findPreviousEntry(this.entry), entries);
                    }
                    else if (this.entry.treeLevel === null) {
                        this.collection.insertAfter(this.entry, entries);
                    }
                    else {
                        let aboveEntry = this.collection.findTreeDescendants(this.entry).pop();
                        if (!aboveEntry) {
                            aboveEntry = this.entry;
                        }
                        this.collection.insertAfter(aboveEntry, entries);
                    }
                    this.moveState.executing = false;
                    this.dingsel(entries);
                }
                dingsel(entries) {
                    Rocket.Display.Entry.findLastMod(Rocket.Cmd.Zone.of(this.elemJq).jQuery).forEach((entry) => {
                        entry.lastMod = false;
                    });
                    let pids = [];
                    for (let entry of entries) {
                        pids.push(entry.id);
                        entry.selector.selected = false;
                        this.dingselAndExecTree(entry);
                        entry.lastMod = true;
                    }
                    let url = new Jhtml.Url(this.elemJq.attr("href")).extR(null, { "pids": pids });
                    Jhtml.Monitor.of(this.elemJq.get(0)).lookupModel(url);
                }
                dingselAndExecTree(entry) {
                    if (entry.treeLevel === null)
                        return;
                    let newTreeLevel;
                    if (this.insertMode == InsertMode.CHILD) {
                        newTreeLevel = (this.entry.treeLevel || 0) + 1;
                    }
                    else {
                        newTreeLevel = this.entry.treeLevel;
                    }
                    let treeLevelDelta = newTreeLevel - entry.treeLevel;
                    entry.treeLevel = newTreeLevel;
                    if (newTreeLevel === null)
                        return;
                    this.moveState.executing = true;
                    let decendants = this.moveState.retrieveTreeDecendants(entry);
                    this.collection.insertAfter(entry, decendants);
                    this.moveState.executing = false;
                    for (let decendant of decendants) {
                        decendant.lastMod = true;
                        decendant.treeLevel += treeLevelDelta;
                    }
                }
            }
            Order.Control = Control;
            let InsertMode;
            (function (InsertMode) {
                InsertMode[InsertMode["BEFORE"] = 0] = "BEFORE";
                InsertMode[InsertMode["AFTER"] = 1] = "AFTER";
                InsertMode[InsertMode["CHILD"] = 2] = "CHILD";
            })(InsertMode = Order.InsertMode || (Order.InsertMode = {}));
            class MoveState {
                constructor() {
                    this.treeMoveStates = [];
                    this._executing = false;
                }
                set executing(executing) {
                    if (this._executing == executing) {
                        throw new Error("Illegal move state");
                    }
                    this._executing = executing;
                }
                get executing() {
                    return this._executing;
                }
                memorizeTreeDecendants(entries) {
                    this.treeMoveStates.splice(0);
                    for (let entry of entries) {
                        if (entry.treeLevel === null)
                            continue;
                        let decendants = [];
                        if (entry.collection) {
                            decendants = entry.collection.findTreeDescendants(entry);
                        }
                        this.treeMoveStates.push({
                            entry: entry,
                            treeDecendantsEntries: decendants
                        });
                    }
                }
                retrieveTreeDecendants(entry) {
                    let moveState = this.treeMoveStates.find((moveState) => {
                        return moveState.entry === entry;
                    });
                    if (moveState) {
                        this.treeMoveStates.splice(this.treeMoveStates.indexOf(moveState), 1);
                        return moveState.treeDecendantsEntries;
                    }
                    throw new Error("illegal move state");
                }
            }
            Order.MoveState = MoveState;
        })(Order = Impl.Order || (Impl.Order = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            class Header {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                init(jqElem) {
                    this.jqElem = jqElem;
                    this.state = new State(this.overviewContent);
                    this.state.draw(this.jqElem.find(".rocket-impl-state:first"));
                    this.quicksearchForm = new QuicksearchForm(this.overviewContent);
                    this.quicksearchForm.init(this.jqElem.find("form.rocket-impl-quicksearch:first"));
                    this.critmodForm = new CritmodForm(this.overviewContent);
                    this.critmodForm.init(this.jqElem.find("form.rocket-impl-critmod:first"));
                    this.critmodSelect = new CritmodSelect(this.overviewContent);
                    this.critmodSelect.init(this.jqElem.find("form.rocket-impl-critmod-select:first"), this.critmodForm);
                    this.critmodForm.drawControl(this.critmodSelect.jQuery.parent());
                }
            }
            Overview.Header = Header;
            class State {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                draw(jqElem) {
                    this.jqElem = jqElem;
                    var that = this;
                    this.jqAllButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqElem);
                    this.jqAllButton.click(function () {
                        that.overviewContent.showAll();
                        that.reDraw();
                    });
                    this.jqSelectedButton = $("<button />", { "type": "button", "class": "btn btn-secondary" }).appendTo(jqElem);
                    this.jqSelectedButton.click(function () {
                        that.overviewContent.showSelected();
                        that.reDraw();
                    });
                    this.reDraw();
                    this.overviewContent.whenContentChanged(function () { that.reDraw(); });
                    this.overviewContent.whenSelectionChanged(function () { that.reDraw(); });
                }
                reDraw() {
                    var numEntries = this.overviewContent.numEntries;
                    if (numEntries == 1) {
                        this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-label"));
                    }
                    else {
                        this.jqAllButton.text(numEntries + " " + this.jqElem.data("entries-plural-label"));
                    }
                    if (this.overviewContent.selectedOnly) {
                        this.jqAllButton.removeClass("active");
                        this.jqSelectedButton.addClass("active");
                    }
                    else {
                        this.jqAllButton.addClass("active");
                        this.jqSelectedButton.removeClass("active");
                    }
                    if (!this.overviewContent.selectable) {
                        this.jqSelectedButton.hide();
                        return;
                    }
                    this.jqSelectedButton.show();
                    var numSelected = this.overviewContent.numSelectedEntries;
                    if (numSelected == 1) {
                        this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-label"));
                    }
                    else {
                        this.jqSelectedButton.text(numSelected + " " + this.jqElem.data("selected-plural-label"));
                    }
                    if (0 == numSelected) {
                        this.jqSelectedButton.prop("disabled", true);
                        return;
                    }
                    this.jqSelectedButton.prop("disabled", false);
                }
            }
            class QuicksearchForm {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                    this.sc = 0;
                    this.serachVal = null;
                }
                init(jqForm) {
                    if (this.form) {
                        throw new Error("Quicksearch already initialized.");
                    }
                    this.jqForm = jqForm;
                    this.form = Jhtml.Ui.Form.from(jqForm.get(0));
                    this.form.on("submit", () => {
                        this.onSubmit();
                    });
                    this.form.config.disableControls = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.successResponseHandler = (response) => {
                        if (!response.model || !response.model.snippet)
                            return false;
                        this.whenSubmitted(response.model.snippet, response.additionalData);
                        return true;
                    };
                    this.initListeners();
                }
                initListeners() {
                    this.form.reset();
                    var jqButtons = this.jqForm.find("button[type=submit]");
                    this.jqSearchButton = $(jqButtons.get(0));
                    var jqClearButton = $(jqButtons.get(1));
                    this.jqSearchInput = this.jqForm.find("input[type=search]:first");
                    var that = this;
                    this.jqSearchInput.on("paste keyup", function () {
                        that.send(false);
                    });
                    this.jqSearchInput.on("change", function () {
                        that.send(true);
                    });
                    jqClearButton.on("click", function () {
                        that.jqSearchInput.val("");
                        that.updateState();
                    });
                }
                updateState() {
                    if (this.jqSearchInput.val().toString().length > 0) {
                        this.jqForm.addClass("rocket-active");
                    }
                    else {
                        this.jqForm.removeClass("rocket-active");
                    }
                }
                send(force) {
                    var searchVal = this.jqSearchInput.val().toString();
                    if (this.serachVal == searchVal)
                        return;
                    this.updateState();
                    this.overviewContent.clear(true);
                    this.serachVal = searchVal;
                    var si = ++this.sc;
                    var that = this;
                    if (force) {
                        that.jqSearchButton.click();
                        return;
                    }
                    setTimeout(function () {
                        if (si !== that.sc)
                            return;
                        that.jqSearchButton.click();
                    }, 300);
                }
                onSubmit() {
                    this.sc++;
                    this.overviewContent.clear(true);
                }
                whenSubmitted(snippet, info) {
                    this.overviewContent.initFromResponse(snippet, info);
                }
            }
            class CritmodSelect {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                get jQuery() {
                    return this.jqForm;
                }
                init(jqForm, critmodForm) {
                    if (this.form) {
                        throw new Error("CritmodSelect already initialized.");
                    }
                    this.jqForm = jqForm;
                    this.form = Jhtml.Ui.Form.from(jqForm.get(0));
                    this.form.reset();
                    this.critmodForm = critmodForm;
                    this.jqButton = jqForm.find("button[type=submit]").hide();
                    this.form.config.disableControls = false;
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.autoSubmitAllowed = false;
                    this.form.config.successResponseHandler = (response) => {
                        if (response.model && response.model.snippet) {
                            this.whenSubmitted(response.model.snippet, response.additionalData);
                            return true;
                        }
                        return false;
                    };
                    this.jqSelect = jqForm.find("select:first").change(() => {
                        this.send();
                    });
                    critmodForm.onChange(() => {
                        this.form.abortSubmit();
                        this.updateId();
                    });
                    critmodForm.whenChanged((idOptions) => {
                        this.updateIdOptions(idOptions);
                    });
                }
                updateState() {
                    if (this.jqSelect.val()) {
                        this.jqForm.addClass("rocket-active");
                    }
                    else {
                        this.jqForm.removeClass("rocket-active");
                    }
                }
                send() {
                    this.form.submit({ button: this.jqButton.get(0) });
                    this.updateState();
                    this.overviewContent.clear(true);
                    var id = this.jqSelect.val();
                    this.critmodForm.activated = id ? true : false;
                    this.critmodForm.critmodSaveId = id.toString();
                    this.critmodForm.freeze();
                }
                whenSubmitted(snippet, info) {
                    this.overviewContent.initFromResponse(snippet, info);
                    this.critmodForm.reload();
                }
                updateId() {
                    var id = this.critmodForm.critmodSaveId;
                    if (id && isNaN(parseInt(id))) {
                        this.jqSelect.append($("<option />", { "value": id, "text": this.critmodForm.critmodSaveName }));
                    }
                    this.jqSelect.val(id);
                    this.updateState();
                }
                updateIdOptions(idOptions) {
                    this.jqSelect.empty();
                    for (let id in idOptions) {
                        this.jqSelect.append($("<option />", { value: id.trim(), text: idOptions[id] }));
                    }
                    this.jqSelect.val(this.critmodForm.critmodSaveId);
                }
            }
            class CritmodForm {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                    this.changeCallbacks = [];
                    this.changedCallbacks = [];
                    this._open = true;
                }
                drawControl(jqControlContainer) {
                    this.jqControlContainer = jqControlContainer;
                    this.jqOpenButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-open-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-filter" }))
                        .click(() => { this.open = true; })
                        .appendTo(jqControlContainer);
                    this.jqEditButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-edit-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-filter" }))
                        .click(() => { this.open = true; })
                        .appendTo(jqControlContainer);
                    this.jqCloseButton = $("<button />", {
                        "class": "btn btn-secondary",
                        "text": jqControlContainer.data("rocket-impl-close-filter-label") + " "
                    })
                        .append($("<i />", { "class": "fa fa-times" }))
                        .click(() => { this.open = false; })
                        .appendTo(jqControlContainer);
                    this.open = false;
                }
                updateControl() {
                    if (!this.jqOpenButton)
                        return;
                    if (this.open) {
                        this.jqControlContainer.addClass("rocket-open");
                        this.jqOpenButton.hide();
                        this.jqEditButton.hide();
                        this.jqCloseButton.show();
                        return;
                    }
                    this.jqControlContainer.removeClass("rocket-open");
                    if (this.critmodSaveId) {
                        this.jqOpenButton.hide();
                        this.jqEditButton.show();
                    }
                    else {
                        this.jqOpenButton.show();
                        this.jqEditButton.hide();
                    }
                    this.jqCloseButton.hide();
                }
                get open() {
                    return this._open;
                }
                set open(open) {
                    this._open = open;
                    if (open) {
                        this.jqForm.show();
                    }
                    else {
                        this.jqForm.hide();
                    }
                    this.updateControl();
                }
                init(jqForm) {
                    if (this.form) {
                        throw new Error("CritmodForm already initialized.");
                    }
                    this.jqForm = jqForm;
                    this.form = Jhtml.Ui.Form.from(jqForm.get(0));
                    this.form.reset();
                    this.form.config.actionUrl = jqForm.data("rocket-impl-post-url");
                    this.form.config.successResponseHandler = (response) => {
                        if (response.model && response.model.snippet) {
                            this.whenSubmitted(response.model.snippet, response.additionalData);
                            return true;
                        }
                        return false;
                    };
                    var activateFunc = (ensureCritmodSaveId) => {
                        this.activated = true;
                        if (ensureCritmodSaveId && !this.critmodSaveId) {
                            this.critmodSaveId = "new";
                        }
                        this.onSubmit();
                    };
                    var deactivateFunc = () => {
                        this.activated = false;
                        this.critmodSaveId = null;
                        this.block();
                        this.onSubmit();
                    };
                    this.jqApplyButton = jqForm.find(".rocket-impl-critmod-apply").click(function () { activateFunc(false); });
                    this.jqClearButton = jqForm.find(".rocket-impl-critmod-clear").click(function () { deactivateFunc(); });
                    this.jqNameInput = jqForm.find(".rocket-impl-critmod-name");
                    this.jqSaveButton = jqForm.find(".rocket-impl-critmod-save").click(function () { activateFunc(true); });
                    this.jqSaveAsButton = jqForm.find(".rocket-impl-critmod-save-as").click(() => {
                        this.critmodSaveId = null;
                        activateFunc(true);
                    });
                    this.jqDeleteButton = jqForm.find(".rocket-impl-critmod-delete").click(function () { deactivateFunc(); });
                    this.updateState();
                }
                get activated() {
                    return this.jqForm.hasClass("rocket-active");
                }
                set activated(activated) {
                    if (activated) {
                        this.jqForm.addClass("rocket-active");
                    }
                    else {
                        this.jqForm.removeClass("rocket-active");
                    }
                }
                get critmodSaveId() {
                    return this.jqForm.data("rocket-impl-critmod-save-id");
                }
                set critmodSaveId(critmodSaveId) {
                    this.jqForm.data("rocket-impl-critmod-save-id", critmodSaveId);
                    this.updateControl();
                }
                get critmodSaveName() {
                    return this.jqNameInput.val().toString();
                }
                updateState() {
                    if (this.critmodSaveId) {
                        this.jqSaveAsButton.show();
                        this.jqDeleteButton.show();
                    }
                    else {
                        this.jqSaveAsButton.hide();
                        this.jqDeleteButton.hide();
                    }
                }
                freeze() {
                    this.form.abortSubmit();
                    this.form.disableControls();
                    this.block();
                }
                block() {
                    if (this.jqBlocker)
                        return;
                    this.jqBlocker = $("<div />", { "class": "rocket-impl-critmod-blocker" })
                        .appendTo(this.jqForm);
                }
                reload() {
                    var url = this.form.config.actionUrl;
                    Jhtml.Monitor.of(this.jqForm.get(0)).lookupModel(Jhtml.Url.create(url)).then((result) => {
                        this.replaceForm(result.model.snippet, result.response.additionalData);
                    });
                }
                onSubmit() {
                    this.changeCallbacks.forEach(function (callback) {
                        callback();
                    });
                    this.overviewContent.clear(true);
                }
                whenSubmitted(snippet, info) {
                    this.overviewContent.init(1);
                    this.replaceForm(snippet, info);
                }
                replaceForm(snippet, info) {
                    if (this.jqBlocker) {
                        this.jqBlocker.remove();
                        this.jqBlocker = null;
                    }
                    var jqForm = $(snippet.elements);
                    this.jqForm.replaceWith(jqForm);
                    this.form = null;
                    snippet.markAttached();
                    this.init(jqForm);
                    this.open = this.open;
                    this.updateControl();
                    var idOptions = info.critmodSaveIdOptions;
                    this.changedCallbacks.forEach(function (callback) {
                        callback(idOptions);
                    });
                }
                onChange(callback) {
                    this.changeCallbacks.push(callback);
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
            }
            ;
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            class OverviewContent {
                constructor(jqElem, loadUrl, stateKey) {
                    this.loadUrl = loadUrl;
                    this.stateKey = stateKey;
                    this.pages = {};
                    this.fakePage = null;
                    this._currentPageNo = null;
                    this.allInfo = null;
                    this.contentChangedCallbacks = [];
                    this.selectorObserver = null;
                    this.loadingPageNos = new Array();
                    this.jqLoader = null;
                    this.collection = Rocket.Display.Collection.from(jqElem);
                    this.selectorState = new SelectorState(this.collection);
                }
                isInit() {
                    return this._currentPageNo != null && this._numPages != null && this._numEntries != null;
                }
                initFromDom(currentPageNo, numPages, numEntries, pageSize) {
                    this.reset(false);
                    this._currentPageNo = currentPageNo;
                    this._numPages = numPages;
                    this._numEntries = numEntries;
                    this._pageSize = pageSize;
                    this.refitPages(currentPageNo);
                    if (this.allInfo) {
                        let O = Object;
                        this.allInfo = new AllInfo(O.values(this.pages), 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                }
                refitPages(startPageNo) {
                    this.pages = {};
                    this.collection.scan();
                    let page = null;
                    let i = 0;
                    for (let entry of this.collection.entries) {
                        if (this.fakePage && this.fakePage.containsEntry(entry)) {
                            continue;
                        }
                        if (0 == i % this.pageSize) {
                            page = this.createPage((i / this._pageSize) + 1);
                            page.entries = [];
                        }
                        page.entries.push(entry);
                        i++;
                    }
                    this.pageVisibilityChanged();
                }
                init(currentPageNo) {
                    this.reset(false);
                    this.goTo(currentPageNo);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([this.pages[currentPageNo]], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                }
                initFromResponse(snippet, info) {
                    this.reset(false);
                    var page = this.createPage(parseInt(info.pageNo));
                    this._currentPageNo = page.pageNo;
                    this.initPageFromResponse([page], snippet, info);
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([page], 0);
                    }
                    this.buildFakePage();
                    this.triggerContentChange();
                }
                clear(showLoader) {
                    this.reset(showLoader);
                    this.triggerContentChange();
                }
                reset(showLoader) {
                    let page = null;
                    for (let pageNo in this.pages) {
                        page = this.pages[pageNo];
                        page.dispose();
                        delete this.pages[pageNo];
                        this.unmarkPageAsLoading(page.pageNo);
                    }
                    this._currentPageNo = null;
                    if (this.fakePage) {
                        this.fakePage.dispose();
                        this.unmarkPageAsLoading(this.fakePage.pageNo);
                        this.fakePage = null;
                    }
                    if (this.allInfo) {
                        this.allInfo = new AllInfo([], 0);
                    }
                    if (showLoader) {
                        this.addLoader();
                    }
                    else {
                        this.removeLoader();
                    }
                }
                initSelector(selectorObserver) {
                    if (this.selectorObserver) {
                        throw new Error("Selector state already activated");
                    }
                    this.selectorObserver = selectorObserver;
                    this.selectorState.activate(selectorObserver);
                    this.triggerContentChange();
                    this.buildFakePage();
                }
                buildFakePage() {
                    if (!this.selectorObserver)
                        return;
                    if (this.fakePage) {
                        throw new Error("Fake page already existing.");
                    }
                    this.fakePage = new Page(0);
                    this.fakePage.hide();
                    var pids = this.selectorObserver.getSelectedIds();
                    var unloadedIds = pids.slice();
                    var that = this;
                    this.collection.entries.forEach(function (entry) {
                        let id = entry.id;
                        let i;
                        if (-1 < (i = unloadedIds.indexOf(id))) {
                            unloadedIds.splice(i, 1);
                        }
                    });
                    this.loadFakePage(unloadedIds);
                    return this.fakePage;
                }
                loadFakePage(unloadedPids) {
                    if (unloadedPids.length == 0) {
                        this.fakePage.entries = [];
                        this.selectorState.observeFakePage(this.fakePage);
                        return;
                    }
                    this.markPageAsLoading(0);
                    let fakePage = this.fakePage;
                    Jhtml.Monitor.of(this.collection.jQuery.get(0))
                        .lookupModel(this.loadUrl.extR(null, { "pids": unloadedPids }))
                        .then((modelResult) => {
                        if (fakePage !== this.fakePage)
                            return;
                        this.unmarkPageAsLoading(0);
                        let model = modelResult.model;
                        let collectionJq = $(model.snippet.elements).find(".rocket-collection:first");
                        model.snippet.elements = collectionJq.children().toArray();
                        fakePage.entries = Rocket.Display.Entry.children(collectionJq);
                        for (let entry of fakePage.entries) {
                            this.collection.jQuery.append(entry.jQuery);
                        }
                        this.collection.scan();
                        model.snippet.markAttached();
                        this.selectorState.observeFakePage(fakePage);
                        this.triggerContentChange();
                    });
                }
                get selectedOnly() {
                    return this.allInfo != null;
                }
                showSelected() {
                    var scrollTop = $("html, body").scrollTop();
                    var visiblePages = new Array();
                    for (let pageNo in this.pages) {
                        let page = this.pages[pageNo];
                        if (page.visible) {
                            visiblePages.push(page);
                        }
                        page.hide();
                    }
                    this.selectorState.showSelectedEntriesOnly();
                    this.selectorState.autoShowSelected = true;
                    if (this.allInfo === null) {
                        this.allInfo = new AllInfo(visiblePages, scrollTop);
                    }
                    this.updateLoader();
                    this.triggerContentChange();
                }
                showAll() {
                    if (this.allInfo === null)
                        return;
                    this.selectorState.hideEntries();
                    this.selectorState.autoShowSelected = false;
                    this.allInfo.pages.forEach(function (page) {
                        page.show();
                    });
                    this.pageVisibilityChanged();
                    $("html, body").scrollTop(this.allInfo.scrollTop);
                    this.allInfo = null;
                    this.updateLoader();
                    this.triggerContentChange();
                }
                get currentPageNo() {
                    return this._currentPageNo;
                }
                get numPages() {
                    return this._numPages;
                }
                get numEntries() {
                    return this._numEntries;
                }
                get pageSize() {
                    return this._pageSize;
                }
                get numSelectedEntries() {
                    if (!this.collection.selectable)
                        return null;
                    if (!this.selectorObserver || (this.fakePage !== null && this.fakePage.isContentLoaded())) {
                        return this.collection.selectedEntries.length;
                    }
                    return this.selectorObserver.getSelectedIds().length;
                }
                get selectable() {
                    return this.collection.selectable;
                }
                setCurrentPageNo(currentPageNo) {
                    if (this._currentPageNo == currentPageNo) {
                        return;
                    }
                    this._currentPageNo = currentPageNo;
                    this.triggerContentChange();
                }
                triggerContentChange() {
                    this.contentChangedCallbacks.forEach((callback) => {
                        callback(this);
                    });
                }
                changeBoundaries(numPages, numEntries, entriesPerPage) {
                    if (this._numPages == numPages && this._numEntries == numEntries
                        && this._pageSize == entriesPerPage) {
                        return;
                    }
                    this._numPages = numPages;
                    this._numEntries = numEntries;
                    if (this.currentPageNo > this.numPages) {
                        this.goTo(this.numPages);
                        return;
                    }
                    this.triggerContentChange();
                }
                whenContentChanged(callback) {
                    this.contentChangedCallbacks.push(callback);
                }
                whenSelectionChanged(callback) {
                    this.selectorState.whenChanged(callback);
                }
                isPageNoValid(pageNo) {
                    return (pageNo > 0 && pageNo <= this.numPages);
                }
                containsPageNo(pageNo) {
                    return this.pages[pageNo] !== undefined;
                }
                applyContents(page, entries) {
                    if (page.entries !== null) {
                        throw new Error("Contents already applied.");
                    }
                    page.entries = entries;
                    for (var pni = page.pageNo - 1; pni > 0; pni--) {
                        if (this.pages[pni] === undefined || !this.pages[pni].isContentLoaded())
                            continue;
                        let aboveJq = this.pages[pni].lastEntry.jQuery;
                        for (let entry of entries) {
                            entry.jQuery.insertAfter(aboveJq);
                            aboveJq = entry.jQuery;
                            this.selectorState.observeEntry(entry);
                        }
                        this.collection.scan();
                        return;
                    }
                    let aboveJq;
                    for (let entry of entries) {
                        if (!aboveJq) {
                            this.collection.jQuery.prepend(entry.jQuery);
                        }
                        else {
                            entry.jQuery.insertAfter(aboveJq);
                        }
                        aboveJq = entry.jQuery;
                        this.selectorState.observeEntry(entry);
                    }
                    this.collection.scan();
                }
                goTo(pageNo) {
                    if (!this.isPageNoValid(pageNo)) {
                        throw new Error("Invalid pageNo: " + pageNo);
                    }
                    if (this.selectedOnly) {
                        throw new Error("No paging support for selected entries.");
                    }
                    if (pageNo === this.currentPageNo) {
                        return;
                    }
                    if (this.pages[pageNo] === undefined) {
                        this.load(pageNo);
                        this.showSingle(pageNo);
                        this.setCurrentPageNo(pageNo);
                        return;
                    }
                    if (this.scrollToPage(this.currentPageNo, pageNo)) {
                        this.setCurrentPageNo(pageNo);
                        return;
                    }
                    this.showSingle(pageNo);
                    this.setCurrentPageNo(pageNo);
                    this.pageVisibilityChanged();
                }
                showSingle(pageNo) {
                    for (var i in this.pages) {
                        if (this.pages[i].pageNo == pageNo) {
                            this.pages[i].show();
                        }
                        else {
                            this.pages[i].hide();
                        }
                    }
                    this.pageVisibilityChanged();
                }
                pageVisibilityChanged() {
                    let startPageNo = null;
                    let numPages = 0;
                    for (let pageNo in this.pages) {
                        if (!this.pages[pageNo].visible)
                            continue;
                        if (!startPageNo) {
                            startPageNo = this.pages[pageNo].pageNo;
                        }
                        numPages++;
                    }
                    if (startPageNo === null)
                        return;
                    let jhtmlPage = Rocket.Cmd.Zone.of(this.collection.jQuery).page;
                    jhtmlPage.loadUrl = jhtmlPage.url.extR((startPageNo != 1 ? startPageNo.toString() : null), { numPages: numPages, stateKey: this.stateKey });
                }
                scrollToPage(pageNo, targetPageNo) {
                    var page = null;
                    if (pageNo < targetPageNo) {
                        for (var i = pageNo; i <= targetPageNo; i++) {
                            if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded()) {
                                return false;
                            }
                            page = this.pages[i];
                            page.show();
                        }
                        this.pageVisibilityChanged();
                    }
                    else {
                        for (var i = pageNo; i >= targetPageNo; i--) {
                            if (!this.containsPageNo(i) || !this.pages[i].isContentLoaded() || !this.pages[i].visible) {
                                return false;
                            }
                            page = this.pages[i];
                        }
                    }
                    $("html, body").stop().animate({
                        scrollTop: page.firstEntry.jQuery.offset().top
                    }, 500);
                    return true;
                }
                markPageAsLoading(pageNo) {
                    if (-1 < this.loadingPageNos.indexOf(pageNo)) {
                        throw new Error("page already loading");
                    }
                    this.loadingPageNos.push(pageNo);
                    this.updateLoader();
                }
                unmarkPageAsLoading(pageNo) {
                    var i = this.loadingPageNos.indexOf(pageNo);
                    if (-1 == i)
                        return;
                    this.loadingPageNos.splice(i, 1);
                    this.updateLoader();
                }
                updateLoader() {
                    for (var i in this.loadingPageNos) {
                        if (this.loadingPageNos[i] == 0 && this.selectedOnly) {
                            this.addLoader();
                            return;
                        }
                        if (this.loadingPageNos[i] > 0 && !this.selectedOnly) {
                            this.addLoader();
                            return;
                        }
                    }
                    this.removeLoader();
                }
                addLoader() {
                    if (this.jqLoader)
                        return;
                    this.jqLoader = $("<div />", { "class": "rocket-impl-overview-loading" })
                        .insertAfter(this.collection.jQuery.parent("table"));
                }
                removeLoader() {
                    if (!this.jqLoader)
                        return;
                    this.jqLoader.remove();
                    this.jqLoader = null;
                }
                createPage(pageNo) {
                    if (this.containsPageNo(pageNo)) {
                        throw new Error("Page already exists: " + pageNo);
                    }
                    var page = this.pages[pageNo] = new Page(pageNo);
                    if (this.selectedOnly) {
                        page.hide();
                    }
                    return page;
                }
                load(pageNo) {
                    var page = this.createPage(pageNo);
                    this.markPageAsLoading(pageNo);
                    Jhtml.Monitor.of(this.collection.jQuery.get(0))
                        .lookupModel(this.loadUrl.extR(null, { "pageNo": pageNo }))
                        .then((modelResult) => {
                        if (page !== this.pages[pageNo])
                            return;
                        this.unmarkPageAsLoading(pageNo);
                        this.initPageFromResponse([page], modelResult.model.snippet, modelResult.response.additionalData);
                        this.triggerContentChange();
                    })
                        .catch(e => {
                        if (page !== this.pages[pageNo])
                            return;
                        this.unmarkPageAsLoading(pageNo);
                        throw e;
                    });
                }
                initPageFromResponse(pages, snippet, data) {
                    this.changeBoundaries(data.numPages, data.numEntries, data.pageSize);
                    let collectionJq = $(snippet.elements).find(".rocket-collection:first");
                    var jqContents = collectionJq.children();
                    snippet.elements = jqContents.toArray();
                    let entries = Rocket.Display.Entry.children(collectionJq);
                    for (let page of pages) {
                        this.applyContents(page, entries.splice(0, this._pageSize));
                    }
                    snippet.markAttached();
                }
            }
            Overview.OverviewContent = OverviewContent;
            class SelectorState {
                constructor(collection) {
                    this.collection = collection;
                    this.fakeEntryMap = {};
                    this._autoShowSelected = false;
                }
                activate(selectorObserver) {
                    if (!selectorObserver)
                        return;
                    this.collection.destroySelectors();
                    this.collection.setupSelector(selectorObserver);
                }
                observeFakePage(fakePage) {
                    fakePage.entries.forEach((entry) => {
                        if (this.collection.containsEntryId(entry.id)) {
                            entry.dispose();
                        }
                        else {
                            this.registerEntry(entry);
                        }
                    });
                }
                observeEntry(entry) {
                    if (this.fakeEntryMap[entry.id]) {
                        this.fakeEntryMap[entry.id].dispose();
                    }
                    this.registerEntry(entry);
                }
                registerEntry(entry, fake = false) {
                    this.collection.registerEntry(entry);
                    if (fake) {
                        this.fakeEntryMap[entry.id] = entry;
                    }
                    if (entry.selector === null)
                        return;
                    if (this.autoShowSelected && entry.selector.selected) {
                        entry.show();
                    }
                    entry.selector.onChanged(() => {
                        if (this.autoShowSelected && entry.selector.selected) {
                            entry.show();
                        }
                    });
                    var onFunc = () => {
                        delete this.fakeEntryMap[entry.id];
                    };
                    entry.on(Rocket.Display.Entry.EventType.DISPOSED, onFunc);
                    entry.on(Rocket.Display.Entry.EventType.REMOVED, onFunc);
                }
                get autoShowSelected() {
                    return this._autoShowSelected;
                }
                set autoShowSelected(showSelected) {
                    this._autoShowSelected = showSelected;
                }
                showSelectedEntriesOnly() {
                    this.collection.entries.forEach(function (entry) {
                        if (entry.selector.selected) {
                            entry.show();
                        }
                        else {
                            entry.hide();
                        }
                    });
                }
                hideEntries() {
                    this.collection.entries.forEach(function (entry) {
                        entry.hide();
                    });
                }
                whenChanged(callback) {
                    this.collection.onSelectionChanged(callback);
                }
            }
            class AllInfo {
                constructor(pages, scrollTop) {
                    this.pages = pages;
                    this.scrollTop = scrollTop;
                }
            }
            class Page {
                constructor(pageNo, entries = null) {
                    this.pageNo = pageNo;
                    this.entries = entries;
                    this._visible = true;
                }
                get visible() {
                    return this._visible;
                }
                containsEntry(entry) {
                    return 0 < this.entries.indexOf(entry);
                }
                show() {
                    this._visible = true;
                    this.disp();
                }
                hide() {
                    this._visible = false;
                    this.disp();
                }
                get firstEntry() {
                    if (!this.entries || !this.entries[0]) {
                        throw new Error("no first entry");
                    }
                    return this.entries[0];
                }
                get lastEntry() {
                    if (!this.entries || this.entries.length == 0) {
                        throw new Error("no last entry");
                    }
                    return this.entries[this.entries.length - 1];
                }
                dispose() {
                    if (!this.isContentLoaded())
                        return;
                    for (let entry of this.entries) {
                        entry.dispose();
                    }
                    this.entries = null;
                }
                isContentLoaded() {
                    return !!this.entries;
                }
                disp() {
                    if (this.entries === null)
                        return;
                    this.entries.forEach((entry) => {
                        if (this.visible) {
                            entry.show();
                        }
                        else {
                            entry.hide();
                        }
                    });
                }
                removeEntryById(id) {
                    for (var i in this.entries) {
                        if (this.entries[i].id != id)
                            continue;
                        this.entries[i].jQuery.remove();
                        this.entries.splice(parseInt(i), 1);
                        return;
                    }
                }
            }
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Overview;
        (function (Overview) {
            var $ = jQuery;
            class OverviewPage {
                constructor(jqContainer, overviewContent) {
                    this.jqContainer = jqContainer;
                    this.overviewContent = overviewContent;
                }
                initSelector(selectorObserver) {
                    this.overviewContent.initSelector(selectorObserver);
                }
                static findAll(jqElem) {
                    var oc = new Array();
                    jqElem.find(".rocket-impl-overview").each(function () {
                        oc.push(OverviewPage.from($(this)));
                    });
                    return oc;
                }
                static from(jqElem) {
                    var overviewPage = jqElem.data("rocketImplOverviewPage");
                    if (overviewPage instanceof OverviewPage) {
                        return overviewPage;
                    }
                    var jqForm = jqElem.children("form");
                    let overviewToolsJq = jqElem.children(".rocket-impl-overview-tools");
                    var overviewContent = new Overview.OverviewContent(jqElem.find("tbody.rocket-collection:first"), Jhtml.Url.create(overviewToolsJq.data("content-url")), overviewToolsJq.data("state-key"));
                    overviewContent.initFromDom(jqElem.data("current-page"), jqElem.data("num-pages"), jqElem.data("num-entries"), jqElem.data("page-size"));
                    var pagination = new Pagination(overviewContent);
                    pagination.draw(Rocket.Cmd.Zone.of(jqForm).menu.asideCommandList.jQuery);
                    var header = new Overview.Header(overviewContent);
                    header.init(jqElem.children(".rocket-impl-overview-tools"));
                    overviewPage = new OverviewPage(jqElem, overviewContent);
                    jqElem.data("rocketImplOverviewPage", overviewPage);
                    return overviewPage;
                }
            }
            Overview.OverviewPage = OverviewPage;
            class Pagination {
                constructor(overviewContent) {
                    this.overviewContent = overviewContent;
                }
                getCurrentPageNo() {
                    return this.overviewContent.currentPageNo;
                }
                getNumPages() {
                    return this.overviewContent.numPages;
                }
                goTo(pageNo) {
                    this.overviewContent.goTo(pageNo);
                    return;
                }
                draw(jqContainer) {
                    var that = this;
                    this.jqPagination = $("<div />", { "class": "rocket-impl-overview-pagination btn-group" });
                    jqContainer.append(this.jqPagination);
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-first btn btn-secondary",
                        "click": function () { that.goTo(1); }
                    }).append($("<span />", { text: 1 })).append(" ").append($("<i />", {
                        "class": "fa fa-step-backward"
                    })));
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-prev btn btn-secondary",
                        "click": function () {
                            if (that.getCurrentPageNo() > 1) {
                                that.goTo(that.getCurrentPageNo() - 1);
                            }
                        }
                    }).append($("<i />", {
                        "class": "fa fa-chevron-left"
                    })));
                    this.jqInput = $("<input />", {
                        "class": "rocket-impl-pagination-no form-control",
                        "type": "text",
                        "value": this.getCurrentPageNo()
                    }).on("change", function () {
                        var pageNo = parseInt(that.jqInput.val().toString());
                        if (pageNo === NaN || !that.overviewContent.isPageNoValid(pageNo)) {
                            that.jqInput.val(that.overviewContent.currentPageNo);
                            return;
                        }
                        that.jqInput.val(pageNo);
                        that.overviewContent.goTo(pageNo);
                    });
                    this.jqPagination.append(this.jqInput);
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-next btn btn-secondary",
                        "click": function () {
                            if (that.getCurrentPageNo() < that.getNumPages()) {
                                that.goTo(that.getCurrentPageNo() + 1);
                            }
                        }
                    }).append($("<i />", {
                        "class": "fa fa-chevron-right"
                    })));
                    this.jqPagination.append($("<button />", {
                        "type": "button",
                        "class": "rocket-impl-pagination-last btn btn-secondary",
                        "click": function () { that.goTo(that.getNumPages()); }
                    }).append($("<i />", {
                        "class": "fa fa-step-forward"
                    })).append(" ").append($("<span />", { text: that.getNumPages() })));
                    let contentChangedCallback = function () {
                        if (!that.overviewContent.isInit() || that.overviewContent.selectedOnly || that.overviewContent.numPages <= 1) {
                            that.jqPagination.hide();
                        }
                        else {
                            that.jqPagination.show();
                        }
                        that.jqInput.val(that.overviewContent.currentPageNo);
                    };
                    this.overviewContent.whenContentChanged(contentChangedCallback);
                    contentChangedCallback();
                }
            }
            class FixedHeader {
                constructor(numEntries) {
                    this.fixed = false;
                    this.numEntries = numEntries;
                }
                getNumEntries() {
                    return this.numEntries;
                }
                draw(jqHeader, jqTable) {
                    this.jqHeader = jqHeader;
                    this.jqTable = jqTable;
                }
                calcDimensions() {
                    this.jqHeader.parent().css("padding-top", null);
                    this.jqHeader.css("position", "relative");
                    var headerOffset = this.jqHeader.offset();
                    this.fixedCssAttrs = {
                        "position": "fixed",
                        "top": $("#rocket-content-container").offset().top,
                        "left": headerOffset.left,
                        "right": $(window).width() - (headerOffset.left + this.jqHeader.outerWidth())
                    };
                    this.scrolled();
                }
                scrolled() {
                    var headerHeight = this.jqHeader.children().outerHeight();
                    if (this.jqTable.offset().top - $(window).scrollTop() <= this.fixedCssAttrs.top + headerHeight) {
                        if (this.fixed)
                            return;
                        this.fixed = true;
                        this.jqHeader.css(this.fixedCssAttrs);
                        this.jqHeader.parent().css("padding-top", headerHeight);
                        this.jqTableClone.show();
                    }
                    else {
                        if (!this.fixed)
                            return;
                        this.fixed = false;
                        this.jqHeader.css({
                            "position": "relative",
                            "top": "",
                            "left": "",
                            "right": ""
                        });
                        this.jqHeader.parent().css("padding-top", "");
                        this.jqTableClone.hide();
                    }
                }
                cloneTableHeader() {
                    this.jqTableClone = this.jqTable.clone();
                    this.jqTableClone.css("margin-bottom", 0);
                    this.jqTableClone.children("tbody").remove();
                    this.jqHeader.append(this.jqTableClone);
                    this.jqTableClone.hide();
                    var jqClonedChildren = this.jqTableClone.children("thead").children("tr").children();
                    this.jqTable.children("thead").children("tr").children().each(function (index) {
                        jqClonedChildren.eq(index).innerWidth($(this).innerWidth());
                    });
                }
            }
        })(Overview = Impl.Overview || (Impl.Overview = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            class AddControlFactory {
                constructor(embeddedEntryRetriever, newLabel, pasteLabel) {
                    this.embeddedEntryRetriever = embeddedEntryRetriever;
                    this.newLabel = newLabel;
                    this.pasteLabel = pasteLabel;
                }
                createAdd() {
                    return AddControl.create(this.newLabel, this.pasteLabel, this.embeddedEntryRetriever, this.pasteStrategy);
                }
            }
            Relation.AddControlFactory = AddControlFactory;
            class AddControl {
                constructor(jqElem, embeddedEntryRetriever, pasteStrategy = null) {
                    this.jqElem = jqElem;
                    this.pasteStrategy = pasteStrategy;
                    this.onNewEntryCallbacks = [];
                    this.jqNewMultiTypeUl = null;
                    this.jqPasteUl = null;
                    this.disposed = false;
                    this.embeddedEntryRetriever = embeddedEntryRetriever;
                    this.initNew();
                    this.initPaste();
                }
                initNew() {
                    this.jqNew = this.jqElem.children(".rocket-impl-new");
                    this.jqNewButton = this.jqNew.children("button");
                    this.jqNewButton.on("mouseenter", () => {
                        this.embeddedEntryRetriever.setPreloadNewsEnabled(true);
                    });
                    this.jqNewButton.on("click", () => {
                        if (this.isLoading())
                            return;
                        if (this.jqNewMultiTypeUl) {
                            this.jqNewMultiTypeUl.toggle();
                            return;
                        }
                        this.block(true);
                        this.embeddedEntryRetriever.lookupNew((embeddedEntry, snippet) => {
                            this.examineNew(embeddedEntry, snippet);
                        }, () => {
                            this.block(false);
                        });
                    });
                }
                initPaste() {
                    this.jqPaste = this.jqElem.children(".rocket-impl-paste");
                    this.jqPasteButton = this.jqPaste.children("button");
                    if (!this.pasteStrategy)
                        return;
                    this.pasteOnChanged = () => {
                        this.syncPasteButton();
                    };
                    this.pasteStrategy.clipboard.onChanged(this.pasteOnChanged);
                    this.jqPasteButton.on("click", () => {
                        if (this.isLoading())
                            return;
                        if (this.jqPasteUl) {
                            this.jqPasteUl.toggle();
                        }
                    });
                    this.syncPasteButton();
                }
                syncPasteButton() {
                    this.hidePaste();
                    let found = false;
                    for (let element of this.pasteStrategy.clipboard.toArray()) {
                        if (-1 == this.pasteStrategy.pastableEiTypeIds.indexOf(element.eiTypeId)) {
                            continue;
                        }
                        this.addPasteOption(element);
                        found = true;
                    }
                }
                addPasteOption(element) {
                    if (!this.jqPasteUl) {
                        this.jqPasteUl = $("<ul />", { "class": "rocket-impl-multi-type-menu" }).appendTo(this.jqPaste).hide();
                        this.jqPaste.show();
                    }
                    this.jqPasteUl.append($("<li />").append($("<button />", {
                        "type": "button",
                        "text": element.identityString,
                        "click": () => {
                            this.pasteElement(element);
                        }
                    })));
                }
                pasteElement(element) {
                    this.block(true);
                    this.jqPasteUl.hide();
                    this.embeddedEntryRetriever.lookupCopy(element.pid, (embeddedEntry, snippet) => {
                        this.fireCallbacks(embeddedEntry);
                        snippet.markAttached();
                        this.block(false);
                    }, () => {
                        this.pasteStrategy.clipboard.remove(element.eiTypeId, element.pid);
                        this.block(false);
                    });
                }
                hidePaste() {
                    this.jqPaste.hide();
                    if (this.jqPasteUl) {
                        this.jqPasteUl.remove();
                        this.jqPasteUl = null;
                    }
                }
                get jQuery() {
                    return this.jqElem;
                }
                block(blocked) {
                    if (blocked) {
                        this.jqNewButton.prop("disabled", true);
                        this.jqPasteButton.prop("disabled", true);
                        this.jqElem.addClass("rocket-impl-loading");
                    }
                    else {
                        this.jqNewButton.prop("disabled", false);
                        this.jqPasteButton.prop("disabled", false);
                        this.jqElem.removeClass("rocket-impl-loading");
                    }
                }
                examineNew(embeddedEntry, snippet) {
                    this.block(false);
                    if (!embeddedEntry.entryForm.multiEiType) {
                        this.fireCallbacks(embeddedEntry);
                        snippet.markAttached();
                        return;
                    }
                    this.newMultiTypeEmbeddedEntry = embeddedEntry;
                    this.jqNewMultiTypeUl = $("<ul />", { "class": "rocket-impl-multi-type-menu" });
                    this.jqNew.append(this.jqNewMultiTypeUl);
                    let typeMap = embeddedEntry.entryForm.typeMap;
                    for (let typeId in typeMap) {
                        this.jqNewMultiTypeUl.append($("<li />").append($("<button />", {
                            "type": "button",
                            "text": typeMap[typeId],
                            "click": () => {
                                embeddedEntry.entryForm.curEiTypeId = typeId;
                                this.jqNewMultiTypeUl.remove();
                                this.jqNewMultiTypeUl = null;
                                this.newMultiTypeEmbeddedEntry = null;
                                this.fireCallbacks(embeddedEntry);
                                snippet.markAttached();
                            }
                        })));
                    }
                }
                dispose() {
                    this.disposed = true;
                    this.jqElem.remove();
                    if (this.newMultiTypeEmbeddedEntry !== null) {
                        this.fireCallbacks(this.newMultiTypeEmbeddedEntry);
                        this.newMultiTypeEmbeddedEntry = null;
                    }
                    if (this.pasteOnChanged) {
                        this.pasteStrategy.clipboard.offChanged(this.pasteOnChanged);
                    }
                }
                isLoading() {
                    return this.jqElem.hasClass("rocket-impl-loading");
                }
                fireCallbacks(embeddedEntry) {
                    if (this.disposed)
                        return;
                    this.onNewEntryCallbacks.forEach(function (callback) {
                        callback(embeddedEntry);
                    });
                }
                onNewEmbeddedEntry(callback) {
                    this.onNewEntryCallbacks.push(callback);
                }
                static create(newLabel, pasteLabel, embeddedEntryRetriever, pasteStrategy = null) {
                    let elemJq = $("<div />", { "class": "rocket-impl-add-entry" })
                        .append($("<div />", { "class": "rocket-impl-new" })
                        .append($("<button />", { "text": newLabel, "type": "button", "class": "btn btn-block btn-secondary" })));
                    if (pasteStrategy) {
                        elemJq.append($("<div />", { "class": "rocket-impl-paste" })
                            .append($("<button />", { "text": pasteLabel, "type": "button", "class": "btn btn-block btn-secondary" })));
                    }
                    return new AddControl(elemJq, embeddedEntryRetriever, pasteStrategy);
                }
            }
            Relation.AddControl = AddControl;
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            class Clipboard {
                constructor() {
                    this.elements = {};
                    this.cbr = new Jhtml.Util.CallbackRegistry();
                }
                clear() {
                    if (this.isEmpty())
                        return;
                    this.elements = {};
                    this.cbr.fire();
                }
                add(eiTypeId, pid, identityString) {
                    this.elements[this.createKey(eiTypeId, pid)] = new ClipboardElement(eiTypeId, pid, identityString);
                    this.cbr.fire();
                }
                remove(eiTypeId, pid) {
                    let key = this.createKey(eiTypeId, pid);
                    if (!this.elements[key])
                        return;
                    delete this.elements[key];
                    this.cbr.fire();
                }
                contains(eiTypeId, pid) {
                    return !!this.elements[this.createKey(eiTypeId, pid)];
                }
                createKey(eiTypeId, pid) {
                    return eiTypeId + ":" + pid;
                }
                onChanged(callback) {
                    this.cbr.on(callback);
                }
                offChanged(callback) {
                    this.cbr.off(callback);
                }
                toArray() {
                    let elements = [];
                    for (let key in this.elements) {
                        elements.push(this.elements[key]);
                    }
                    return elements;
                }
                isEmpty() {
                    for (let key in this.elements)
                        return false;
                    return true;
                }
            }
            Relation.Clipboard = Clipboard;
            class ClipboardElement {
                constructor(eiTypeId, pid, identityString) {
                    this.eiTypeId = eiTypeId;
                    this.pid = pid;
                    this.identityString = identityString;
                }
            }
            Relation.ClipboardElement = ClipboardElement;
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            class EmbeddedEntry {
                constructor(jqEntry, readOnly, sortable, copyable = false) {
                    this.readOnly = readOnly;
                    this.copyCbr = new Jhtml.Util.CallbackRegistry();
                    this._entry = null;
                    this.entryGroup = Rocket.Display.StructureElement.from(jqEntry, true);
                    let groupJq = jqEntry.children(".rocket-impl-body");
                    if (groupJq.length == 0) {
                        groupJq = jqEntry;
                    }
                    this.bodyGroup = Rocket.Display.StructureElement.from(groupJq, true);
                    this.jqOrderIndex = jqEntry.children(".rocket-impl-order-index").hide();
                    this.jqSummary = jqEntry.children(".rocket-impl-summary");
                    this.jqPageCommands = this.bodyGroup.jQuery.children(".rocket-zone-commands");
                    let rcl = new Rocket.Display.CommandList(this.jqSummary.children(".rocket-simple-commands"), true);
                    let tbse = null;
                    if (!this.bodyGroup.isGroup() && null !== (tbse = Rocket.Display.StructureElement.findFirst(groupJq))) {
                        this.toolbar = tbse.getToolbar(true);
                    }
                    else {
                        this.toolbar = this.bodyGroup.getToolbar(true);
                    }
                    let ecl = this.toolbar.getCommandList();
                    if (copyable) {
                        let config = {
                            iconType: "fa fa-copy", label: "Copy",
                            severity: Rocket.Display.Severity.WARNING
                        };
                        let onClick = () => {
                            this.copied = !this.copied;
                        };
                        this.jqExpCopyButton = ecl.createJqCommandButton(config).click(onClick);
                        this.jqRedCopyButton = rcl.createJqCommandButton(config).click(onClick);
                    }
                    if (readOnly) {
                        this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-file", label: "Detail",
                            severity: Rocket.Display.Severity.SECONDARY });
                    }
                    else {
                        this._entryForm = Rocket.Display.EntryForm.firstOf(jqEntry);
                        if (sortable) {
                            this.jqExpMoveUpButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-up", label: "Move up" });
                            this.jqExpMoveDownButton = ecl.createJqCommandButton({ iconType: "fa fa-arrow-down", label: "Move down" });
                        }
                        this.jqExpRemoveButton = ecl.createJqCommandButton({ iconType: "fa fa-trash-o", label: "Remove",
                            severity: Rocket.Display.Severity.DANGER });
                        this.jqRedFocusButton = rcl.createJqCommandButton({ iconType: "fa fa-pencil", label: "Edit",
                            severity: Rocket.Display.Severity.WARNING });
                        this.jqRedRemoveButton = rcl.createJqCommandButton({ iconType: "fa fa-trash-o", label: "Remove",
                            severity: Rocket.Display.Severity.DANGER });
                        let formElemsJq = this.bodyGroup.jQuery.find("input, textarea, select, button");
                        let changedCallback = () => {
                            this.changed();
                            formElemsJq.off("change", changedCallback);
                        };
                        formElemsJq.on("change", changedCallback);
                    }
                    if (!sortable) {
                        jqEntry.find(".rocket-impl-handle:first").addClass("rocket-not-sortable");
                    }
                    this.reduce();
                    jqEntry.data("rocketImplEmbeddedEntry", this);
                    if (this.toolbar.isEmpty()) {
                        this.toolbar.hide();
                    }
                }
                get entryForm() {
                    return this._entryForm;
                }
                onMove(callback) {
                    if (this.readOnly || !this.jqExpMoveUpButton)
                        return;
                    this.jqExpMoveUpButton.click(function () {
                        callback(true);
                    });
                    this.jqExpMoveDownButton.click(function () {
                        callback(false);
                    });
                }
                onRemove(callback) {
                    if (this.readOnly)
                        return;
                    this.jqExpRemoveButton.click(function () {
                        callback();
                    });
                    this.jqRedRemoveButton.click(function () {
                        callback();
                    });
                }
                onFocus(callback) {
                    this.jqRedFocusButton.click(function () {
                        callback();
                    });
                    this.bodyGroup.onShow(function () {
                        callback();
                    });
                }
                get copyable() {
                    return !!this.jqExpCopyButton;
                }
                get copied() {
                    return this.jqExpCopyButton && this.jqExpCopyButton.hasClass("active");
                }
                set copied(copied) {
                    if (!this.jqExpCopyButton) {
                        throw new Error("Not copyable.");
                    }
                    if (this.copied == copied)
                        return;
                    if (copied) {
                        this.jqExpCopyButton.addClass("active");
                        this.jqRedCopyButton.addClass("active");
                    }
                    else {
                        this.jqExpCopyButton.removeClass("active");
                        this.jqRedCopyButton.removeClass("active");
                    }
                    this.copyCbr.fire();
                }
                onCopy(callback) {
                    if (!this.jqRedCopyButton) {
                        throw new Error("EmbeddedEntry not copyable.");
                    }
                    this.copyCbr.on(callback);
                }
                get jQuery() {
                    return this.entryGroup.jQuery;
                }
                getExpandedCommandList() {
                    return this.toolbar.getCommandList();
                }
                expand(asPartOfList = true) {
                    this.entryGroup.show();
                    this.jqSummary.hide();
                    this.bodyGroup.show();
                    if (asPartOfList) {
                        this.jqPageCommands.hide();
                    }
                    else {
                        this.jqPageCommands.show();
                    }
                    if (this.readOnly)
                        return;
                    if (asPartOfList) {
                        if (this.jqExpMoveUpButton)
                            this.jqExpMoveUpButton.show();
                        if (this.jqExpMoveDownButton)
                            this.jqExpMoveDownButton.show();
                        this.jqExpRemoveButton.show();
                        this.jqPageCommands.hide();
                    }
                    else {
                        if (this.jqExpMoveUpButton)
                            this.jqExpMoveUpButton.hide();
                        if (this.jqExpMoveDownButton)
                            this.jqExpMoveDownButton.hide();
                        this.jqExpRemoveButton.hide();
                        this.jqPageCommands.show();
                    }
                }
                reduce() {
                    this.entryGroup.show();
                    this.jqSummary.show();
                    this.bodyGroup.hide();
                    let jqContentType = this.jqSummary.find(".rocket-impl-content-type:first");
                    if (this.entryForm) {
                        jqContentType.children("span").text(this.entryForm.curGenericLabel);
                        jqContentType.children("i").attr("class", this.entryForm.curGenericIconType);
                    }
                    this.entryGroup.type = Rocket.Display.StructureElement.Type.NONE;
                }
                hide() {
                    this.entryGroup.hide();
                }
                setOrderIndex(orderIndex) {
                    this.jqOrderIndex.val(orderIndex);
                }
                getOrderIndex() {
                    return parseInt(this.jqOrderIndex.val());
                }
                setMoveUpEnabled(enabled) {
                    if (this.readOnly || !this.jqExpMoveUpButton)
                        return;
                    if (enabled) {
                        this.jqExpMoveUpButton.show();
                    }
                    else {
                        this.jqExpMoveUpButton.hide();
                    }
                }
                setMoveDownEnabled(enabled) {
                    if (this.readOnly || !this.jqExpMoveDownButton)
                        return;
                    if (enabled) {
                        this.jqExpMoveDownButton.show();
                    }
                    else {
                        this.jqExpMoveDownButton.hide();
                    }
                }
                dispose() {
                    this.jQuery.remove();
                }
                changed() {
                    let divJq = this.jqSummary.children(".rocket-impl-content");
                    divJq.empty();
                    divJq.append($("<div />", { "class": "rocket-impl-status", "text": this.jQuery.data("rocket-impl-changed-text") }));
                }
                get entry() {
                    if (this._entry)
                        return this._entry;
                    return this._entry = Rocket.Display.Entry.find(this.jQuery, true);
                }
            }
            Relation.EmbeddedEntry = EmbeddedEntry;
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            class EmbeddedEntryRetriever {
                constructor(lookupUrlStr, propertyPath, draftMode, startKey = null, keyPrefix = null) {
                    this.preloadNewsEnabled = false;
                    this.preloadedResponseObjects = new Array();
                    this.pendingNewLookups = new Array();
                    this.sortable = false;
                    this.grouped = true;
                    this.urlStr = lookupUrlStr;
                    this.propertyPath = propertyPath;
                    this.draftMode = draftMode;
                    this.startKey = startKey;
                    this.keyPrefix = keyPrefix;
                }
                setPreloadNewsEnabled(preloadNewsEnabled) {
                    if (!this.preloadNewsEnabled && preloadNewsEnabled && this.preloadedResponseObjects.length == 0) {
                        this.loadNew();
                    }
                    this.preloadNewsEnabled = preloadNewsEnabled;
                }
                lookupNew(doneCallback, failCallback = null) {
                    this.pendingNewLookups.push({ "doneCallback": doneCallback, "failCallback": failCallback });
                    this.checkNew();
                    this.loadNew();
                }
                checkNew() {
                    if (this.pendingNewLookups.length == 0 || this.preloadedResponseObjects.length == 0)
                        return;
                    var pendingLookup = this.pendingNewLookups.shift();
                    let snippet = this.preloadedResponseObjects.shift();
                    var embeddedEntry = new Relation.EmbeddedEntry($(snippet.elements), false, this.sortable);
                    pendingLookup.doneCallback(embeddedEntry, snippet);
                }
                loadNew() {
                    let url = Jhtml.Url.create(this.urlStr).extR("newmappingform", {
                        "propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
                        "draft": this.draftMode ? 1 : 0,
                        "grouped": this.grouped ? 1 : 0
                    });
                    Jhtml.lookupModel(url)
                        .then((result) => {
                        this.doneNewResponse(result.model.snippet);
                    })
                        .catch(e => {
                        this.failNewResponse();
                        throw e;
                    });
                }
                failNewResponse() {
                    if (this.pendingNewLookups.length == 0)
                        return;
                    var pendingLookup = this.pendingNewLookups.shift();
                    if (pendingLookup.failCallback !== null) {
                        pendingLookup.failCallback();
                    }
                }
                doneNewResponse(snippet) {
                    this.preloadedResponseObjects.push(snippet);
                    this.checkNew();
                }
                lookupCopy(pid, doneCallback, failCallback = null) {
                    let url = Jhtml.Url.create(this.urlStr).extR("copymappingform", {
                        "pid": pid,
                        "propertyPath": this.propertyPath + (this.startKey !== null ? "[" + this.keyPrefix + (this.startKey++) + "]" : ""),
                        "grouped": this.grouped ? 1 : 0
                    });
                    Jhtml.lookupModel(url)
                        .then((result) => {
                        let snippet = result.model.snippet;
                        doneCallback(new Relation.EmbeddedEntry($(snippet.elements), false, this.sortable), snippet);
                    })
                        .catch(e => {
                        failCallback();
                        throw e;
                    });
                }
            }
            Relation.EmbeddedEntryRetriever = EmbeddedEntryRetriever;
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            var cmd = Rocket.Cmd;
            var display = Rocket.Display;
            var $ = jQuery;
            class ToMany {
                constructor(selector = null, embedded = null) {
                    this.selector = selector;
                    this.embedded = embedded;
                }
                static from(jqToMany, clipboard = null) {
                    var toMany = jqToMany.data("rocketImplToMany");
                    if (toMany instanceof ToMany) {
                        return toMany;
                    }
                    let toManySelector = null;
                    let jqSelector = jqToMany.children(".rocket-impl-selector");
                    if (jqSelector.length > 0) {
                        toManySelector = new ToManySelector(jqSelector, jqSelector.find("li.rocket-new-entry").detach());
                        jqSelector.find("ul li").each(function () {
                            var entry = new SelectedEntry($(this));
                            entry.label = toManySelector.determineIdentityString(entry.pid);
                            toManySelector.addSelectedEntry(entry);
                        });
                    }
                    var jqCurrents = jqToMany.children(".rocket-impl-currents");
                    var jqNews = jqToMany.children(".rocket-impl-news");
                    let jqEntries = jqToMany.children(".rocket-impl-entries");
                    var addControlFactory = null;
                    let toManyEmbedded = null;
                    let entryFormRetriever = null;
                    if (jqCurrents.length > 0 || jqNews.length > 0 || jqEntries.length > 0) {
                        if (jqNews.length > 0) {
                            var propertyPath = jqNews.data("property-path");
                            var startKey = 0;
                            var testPropertyPath = propertyPath + "[n";
                            jqNews.find("input, textarea").each(function () {
                                var name = $(this).attr("name");
                                if (0 == name.indexOf(testPropertyPath)) {
                                    name = name.substring(testPropertyPath.length);
                                    name.match(/^[0-9]+/).forEach(function (key) {
                                        var curKey = parseInt(key);
                                        if (curKey >= startKey) {
                                            startKey = curKey + 1;
                                        }
                                    });
                                }
                            });
                            entryFormRetriever = new Relation.EmbeddedEntryRetriever(jqNews.data("new-entry-form-url"), propertyPath, jqNews.data("draftMode"), startKey, "n");
                            addControlFactory = new Relation.AddControlFactory(entryFormRetriever, jqNews.data("add-item-label"), jqNews.data("paste-item-label"));
                            let eiTypeIds = jqNews.data("ei-type-range");
                            if (clipboard && eiTypeIds) {
                                addControlFactory.pasteStrategy = {
                                    clipboard: clipboard,
                                    pastableEiTypeIds: eiTypeIds
                                };
                            }
                        }
                        toManyEmbedded = new ToManyEmbedded(jqToMany, addControlFactory, clipboard);
                        if (entryFormRetriever) {
                            entryFormRetriever.sortable = toManyEmbedded.sortable;
                        }
                        jqCurrents.children(".rocket-impl-entry").each(function () {
                            toManyEmbedded.addEntry(new Relation.EmbeddedEntry($(this), toManyEmbedded.isReadOnly(), toManyEmbedded.sortable, !!clipboard));
                        });
                        jqNews.children(".rocket-impl-entry").each(function () {
                            toManyEmbedded.addEntry(new Relation.EmbeddedEntry($(this), toManyEmbedded.isReadOnly(), toManyEmbedded.sortable, false));
                        });
                        jqEntries.children(".rocket-impl-entry").each(function () {
                            toManyEmbedded.addEntry(new Relation.EmbeddedEntry($(this), true, false, !!clipboard));
                        });
                    }
                    var toMany = new ToMany(toManySelector, toManyEmbedded);
                    jqToMany.data("rocketImplToMany", toMany);
                    return toMany;
                }
            }
            Relation.ToMany = ToMany;
            class ToManySelector {
                constructor(jqElem, jqNewEntrySkeleton) {
                    this.jqElem = jqElem;
                    this.jqNewEntrySkeleton = jqNewEntrySkeleton;
                    this.entries = new Array();
                    this.browserLayer = null;
                    this.browserSelectorObserver = null;
                    this.resetButtonJq = null;
                    this.jqElem = jqElem;
                    this.jqUl = jqElem.children("ul");
                    this.originalPids = jqElem.data("original-ei-ids");
                    this.identityStrings = jqElem.data("identity-strings");
                    this.init();
                }
                determineIdentityString(pid) {
                    return this.identityStrings[pid];
                }
                init() {
                    var jqCommandList = $("<div />");
                    this.jqElem.append(jqCommandList);
                    var that = this;
                    var commandList = new display.CommandList(jqCommandList);
                    commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
                        .mouseenter(function () {
                        that.loadBrowser();
                    })
                        .click(function () {
                        that.openBrowser();
                    });
                    this.resetButtonJq = commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
                        .click(function () {
                        that.reset();
                    })
                        .hide();
                    commandList.createJqCommandButton({ label: this.jqElem.data("clear-label") }).click(function () {
                        that.clear();
                    });
                }
                createSelectedEntry(pid, identityString = null) {
                    var entry = new SelectedEntry(this.jqNewEntrySkeleton.clone().appendTo(this.jqUl));
                    entry.pid = pid;
                    if (identityString !== null) {
                        entry.label = identityString;
                    }
                    else {
                        entry.label = this.determineIdentityString(pid);
                    }
                    this.addSelectedEntry(entry);
                    return entry;
                }
                addSelectedEntry(entry) {
                    this.entries.push(entry);
                    var that = this;
                    entry.commandList.createJqCommandButton({ iconType: "fa fa-trash-o", label: this.jqElem.data("remove-entry-label") }).click(function () {
                        that.removeSelectedEntry(entry);
                    });
                }
                removeSelectedEntry(entry) {
                    for (var i in this.entries) {
                        if (this.entries[i] !== entry)
                            continue;
                        entry.jQuery.remove();
                        this.entries.splice(parseInt(i), 1);
                    }
                }
                reset() {
                    this.clear();
                    for (let pid of this.originalPids) {
                        this.createSelectedEntry(pid);
                    }
                    this.manageReset();
                }
                clear() {
                    for (var i in this.entries) {
                        this.entries[i].jQuery.remove();
                    }
                    this.entries.splice(0, this.entries.length);
                    this.manageReset();
                }
                manageReset() {
                    this.resetButtonJq.hide();
                    if (this.originalPids.length != this.entries.length) {
                        this.resetButtonJq.show();
                        return;
                    }
                    for (let entry of this.entries) {
                        if (-1 < this.originalPids.indexOf(entry.pid))
                            continue;
                        this.resetButtonJq.show();
                        return;
                    }
                }
                loadBrowser() {
                    if (this.browserLayer !== null)
                        return;
                    var that = this;
                    this.browserLayer = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqElem));
                    this.browserLayer.hide();
                    this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
                        that.browserLayer = null;
                        that.browserSelectorObserver = null;
                    });
                    let url = this.jqElem.data("overview-tools-url");
                    this.browserLayer.monitor.exec(url).then(() => {
                        let zone = this.browserLayer.getZoneByUrl(url);
                        this.iniBrowserZone(zone);
                        zone.on(Rocket.Cmd.Zone.EventType.CONTENT_CHANGED, () => {
                            this.iniBrowserZone(zone);
                        });
                    });
                }
                iniBrowserZone(zone) {
                    if (this.browserLayer === null)
                        return;
                    var ocs = Impl.Overview.OverviewPage.findAll(zone.jQuery);
                    if (ocs.length == 0)
                        return;
                    ocs[0].initSelector(this.browserSelectorObserver = new Rocket.Display.MultiEntrySelectorObserver());
                    zone.menu.zoneCommandsJq.find(".rocket-important").removeClass("rocket-important");
                    var that = this;
                    zone.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("select-label"), severity: Rocket.Display.Severity.PRIMARY, important: true }).click(function () {
                        that.updateSelection();
                        zone.layer.hide();
                    });
                    zone.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(function () {
                        zone.layer.hide();
                    });
                    this.updateBrowser();
                }
                openBrowser() {
                    this.loadBrowser();
                    this.updateBrowser();
                    this.browserLayer.show();
                }
                updateBrowser() {
                    if (this.browserSelectorObserver === null)
                        return;
                    var selectedIds = new Array();
                    this.entries.forEach(function (entry) {
                        selectedIds.push(entry.pid);
                    });
                    this.browserSelectorObserver.setSelectedIds(selectedIds);
                }
                updateSelection() {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.clear();
                    var that = this;
                    this.browserSelectorObserver.getSelectedIds().forEach(function (id) {
                        var identityString = that.browserSelectorObserver.getIdentityStringById(id);
                        if (identityString !== null) {
                            that.createSelectedEntry(id, identityString);
                            return;
                        }
                        that.createSelectedEntry(id);
                    });
                    this.manageReset();
                }
            }
            class SelectedEntry {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    jqElem.prepend(this.jqLabel = $("<span />"));
                    this.cmdList = new display.CommandList($("<div />").appendTo(jqElem), true);
                    this.jqInput = jqElem.children("input").hide();
                }
                get jQuery() {
                    return this.jqElem;
                }
                get commandList() {
                    return this.cmdList;
                }
                get label() {
                    return this.jqLabel.text();
                }
                set label(label) {
                    this.jqLabel.text(label);
                }
                get pid() {
                    return this.jqInput.val().toString();
                }
                set pid(pid) {
                    this.jqInput.val(pid);
                }
            }
            class ToManyEmbedded {
                constructor(jqToMany, addControlFactory = null, clipboard = null) {
                    this.clipboard = clipboard;
                    this.reduceEnabled = true;
                    this.sortable = true;
                    this.min = null;
                    this.max = null;
                    this.entries = new Array();
                    this.expandZone = null;
                    this.dominantEntry = null;
                    this.firstAddControl = null;
                    this.lastAddControl = null;
                    this.entryAddControls = new Array();
                    this.clearClipboard = true;
                    this.syncing = false;
                    this.jqToMany = jqToMany;
                    this.addControlFactory = addControlFactory;
                    this.reduceEnabled = (true == jqToMany.data("reduced"));
                    this.sortable = (true == jqToMany.data("sortable"));
                    this.closeLabel = jqToMany.data("close-label");
                    this.min = jqToMany.data("min") || null;
                    this.max = jqToMany.data("max") || null;
                    this.jqEmbedded = $("<div />", {
                        "class": "rocket-impl-embedded"
                    });
                    let jqGroup = this.jqToMany.children(".rocket-group").children(".rocket-control");
                    if (jqGroup.length > 0) {
                        this.embeddedContainerJq = jqGroup;
                    }
                    else {
                        this.embeddedContainerJq = this.jqToMany;
                    }
                    this.embeddedContainerJq.append(this.jqEmbedded);
                    this.jqEntries = $("<div />");
                    this.jqEmbedded.append(this.jqEntries);
                    if (this.reduceEnabled) {
                        var structureElement = Rocket.Display.StructureElement.of(this.jqEmbedded);
                        structureElement.type = Rocket.Display.StructureElement.Type.LIGHT_GROUP;
                        var toolbar = structureElement.getToolbar(true).show();
                        var jqButton = null;
                        if (this.isReadOnly()) {
                            jqButton = toolbar.getCommandList().createJqCommandButton({
                                iconType: "fa fa-file",
                                label: jqToMany.data("show-all-label"),
                                important: true,
                                labelImportant: true
                            });
                        }
                        else {
                            jqButton = toolbar.getCommandList().createJqCommandButton({
                                iconType: "fa fa-pencil",
                                label: jqToMany.data("edit-all-label"),
                                severity: display.Severity.WARNING,
                                important: true,
                                labelImportant: true
                            });
                        }
                        let that = this;
                        jqButton.click(function () {
                            that.expand();
                        });
                    }
                    if (this.sortable) {
                        this.initSortable();
                    }
                    this.initClipboard();
                    this.changed();
                }
                isReadOnly() {
                    return this.addControlFactory === null;
                }
                changed() {
                    for (let i in this.entries) {
                        let index = parseInt(i);
                        this.entries[index].setOrderIndex(index);
                        if (this.isPartialExpaned())
                            continue;
                        this.entries[index].setMoveUpEnabled(index > 0);
                        this.entries[index].setMoveDownEnabled(index < this.entries.length - 1);
                    }
                    Rocket.scan();
                    if (this.addControlFactory === null)
                        return;
                    let entryAddControl = null;
                    while (entryAddControl = this.entryAddControls.pop()) {
                        entryAddControl.dispose();
                    }
                    if (this.max && this.max <= this.entries.length) {
                        if (this.firstAddControl !== null) {
                            this.firstAddControl.dispose();
                            this.firstAddControl = null;
                        }
                        if (this.lastAddControl !== null) {
                            this.lastAddControl.dispose();
                            this.lastAddControl = null;
                        }
                        return;
                    }
                    if (this.entries.length === 0 && this.firstAddControl !== null) {
                        this.firstAddControl.dispose();
                        this.firstAddControl = null;
                    }
                    if (this.entries.length > 0 && this.firstAddControl === null) {
                        this.firstAddControl = this.createFirstAddControl();
                    }
                    if (this.isExpanded() && !this.isPartialExpaned()) {
                        for (var i in this.entries) {
                            if (parseInt(i) == 0)
                                continue;
                            this.entryAddControls.push(this.createEntryAddControl(this.entries[i]));
                        }
                    }
                    if (this.lastAddControl === null) {
                        this.lastAddControl = this.createLastAddControl();
                    }
                    if (this.isPartialExpaned()) {
                        if (this.firstAddControl !== null) {
                            this.firstAddControl.jQuery.hide();
                        }
                        this.lastAddControl.jQuery.hide();
                    }
                    else if (!this.isExpanded()) {
                        if (this.firstAddControl !== null) {
                            this.firstAddControl.jQuery.hide();
                        }
                        this.lastAddControl.jQuery.show();
                    }
                    else {
                        if (this.firstAddControl !== null) {
                            this.firstAddControl.jQuery.show();
                        }
                        this.lastAddControl.jQuery.show();
                    }
                }
                createFirstAddControl() {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.jqEmbedded.prepend(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.insertEntry(newEntry);
                    });
                    return addControl;
                }
                createEntryAddControl(entry) {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.entryAddControls.push(addControl);
                    addControl.jQuery.insertBefore(entry.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.insertEntry(newEntry, entry);
                    });
                    return addControl;
                }
                createLastAddControl() {
                    var addControl = this.addControlFactory.createAdd();
                    var that = this;
                    this.jqEmbedded.append(addControl.jQuery);
                    addControl.onNewEmbeddedEntry(function (newEntry) {
                        that.addEntry(newEntry);
                        if (!that.isExpanded()) {
                            that.expand(newEntry);
                        }
                    });
                    return addControl;
                }
                insertEntry(entry, beforeEntry = null) {
                    entry.jQuery.detach();
                    if (beforeEntry === null) {
                        this.entries.unshift(entry);
                        this.jqEntries.prepend(entry.jQuery);
                    }
                    else {
                        entry.jQuery.insertBefore(beforeEntry.jQuery);
                        this.entries.splice(beforeEntry.getOrderIndex(), 0, entry);
                    }
                    this.initEntry(entry);
                    this.changed();
                }
                addEntry(entry) {
                    entry.setOrderIndex(this.entries.length);
                    this.entries.push(entry);
                    this.jqEntries.append(entry.jQuery);
                    this.initEntry(entry);
                    if (this.isReadOnly())
                        return;
                    this.changed();
                }
                switchIndex(oldIndex, newIndex) {
                    let entry = this.entries[oldIndex];
                    this.entries.splice(oldIndex, 1);
                    this.entries.splice(newIndex, 0, entry);
                    this.changed();
                }
                initEntry(entry) {
                    if (this.isExpanded()) {
                        entry.expand();
                    }
                    else {
                        entry.reduce();
                    }
                    var that = this;
                    entry.onMove(function (up) {
                        var oldIndex = entry.getOrderIndex();
                        var newIndex = up ? oldIndex - 1 : oldIndex + 1;
                        if (newIndex < 0 || newIndex >= that.entries.length) {
                            return;
                        }
                        if (up) {
                            that.entries[oldIndex].jQuery.insertBefore(that.entries[newIndex].jQuery);
                        }
                        else {
                            that.entries[oldIndex].jQuery.insertAfter(that.entries[newIndex].jQuery);
                        }
                        that.switchIndex(oldIndex, newIndex);
                    });
                    entry.onRemove(function () {
                        that.entries.splice(entry.getOrderIndex(), 1);
                        entry.jQuery.remove();
                        that.changed();
                    });
                    entry.onFocus(function () {
                        that.expand(entry);
                    });
                    this.initCopy(entry);
                }
                initSortable() {
                    var that = this;
                    var oldIndex = 0;
                    this.jqEntries.sortable({
                        "handle": ".rocket-impl-handle",
                        "forcePlaceholderSize": true,
                        "placeholder": "rocket-impl-entry rocket-impl-entry-placeholder",
                        "start": function (event, ui) {
                            oldIndex = ui.item.index();
                        },
                        "update": function (event, ui) {
                            var newIndex = ui.item.index();
                            that.switchIndex(oldIndex, newIndex);
                        }
                    }).disableSelection();
                }
                enabledSortable() {
                    this.jqEntries.sortable("enable");
                    this.jqEntries.disableSelection();
                }
                disableSortable() {
                    this.jqEntries.sortable("disable");
                    this.jqEntries.enableSelection();
                }
                isExpanded() {
                    return this.expandZone !== null || !this.reduceEnabled;
                }
                isPartialExpaned() {
                    return this.dominantEntry !== null;
                }
                expand(dominantEntry = null) {
                    if (this.isExpanded())
                        return;
                    if (this.sortable) {
                        this.disableSortable();
                    }
                    this.dominantEntry = dominantEntry;
                    this.expandZone = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqToMany))
                        .createZone(window.location.href);
                    this.jqEmbedded.detach();
                    let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
                    this.expandZone.applyContent(contentJq);
                    $("<header></header>").insertBefore(contentJq);
                    this.expandZone.layer.pushHistoryEntry(window.location.href);
                    for (let i in this.entries) {
                        if (dominantEntry === null) {
                            this.entries[i].expand(true);
                        }
                        else if (dominantEntry === this.entries[i]) {
                            this.entries[i].expand(false);
                        }
                        else {
                            this.entries[i].hide();
                        }
                    }
                    var that = this;
                    var jqCommandButton = this.expandZone.menu.mainCommandList
                        .createJqCommandButton({ iconType: "fa fa-trash-o", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                    jqCommandButton.click(function () {
                        that.expandZone.layer.close();
                    });
                    this.expandZone.on(cmd.Zone.EventType.CLOSE, function () {
                        that.reduce();
                    });
                    this.changed();
                }
                reduce() {
                    if (!this.isExpanded())
                        return;
                    this.dominantEntry = null;
                    this.expandZone = null;
                    this.jqEmbedded.detach();
                    this.embeddedContainerJq.append(this.jqEmbedded);
                    for (let i in this.entries) {
                        this.entries[i].reduce();
                    }
                    if (this.sortable) {
                        this.enabledSortable();
                    }
                    this.changed();
                }
                initCopy(entry) {
                    if (!this.clipboard || !entry.copyable)
                        return;
                    let diEntry = entry.entry;
                    if (!diEntry) {
                        throw new Error("No display entry available.");
                    }
                    entry.copied = this.clipboard.contains(diEntry.eiTypeId, diEntry.pid);
                    entry.onRemove(() => {
                        this.clipboard.remove(diEntry.eiTypeId, diEntry.pid);
                    });
                    entry.onCopy(() => {
                        if (this.syncing)
                            return;
                        this.syncing = true;
                        if (!entry.copied) {
                            this.clipboard.remove(diEntry.eiTypeId, diEntry.pid);
                            this.syncing = false;
                            return;
                        }
                        if (this.clearClipboard) {
                            this.clipboard.clear();
                            this.clearClipboard = false;
                        }
                        this.clipboard.add(diEntry.eiTypeId, diEntry.pid, diEntry.identityString);
                        this.syncing = false;
                    });
                }
                initClipboard() {
                    if (!this.clipboard)
                        return;
                    let onChanged = () => {
                        this.syncCopy();
                    };
                    this.clipboard.onChanged(onChanged);
                    Rocket.Cmd.Zone.of(this.jqToMany).page.on("disposed", () => {
                        this.clipboard.offChanged(onChanged);
                        if (this.firstAddControl) {
                            this.firstAddControl.dispose();
                            this.firstAddControl = null;
                        }
                        if (this.lastAddControl) {
                            this.lastAddControl.dispose();
                            this.lastAddControl = null;
                        }
                        let entryAddControl;
                        while (entryAddControl = this.entryAddControls.pop()) {
                            entryAddControl.dispose();
                        }
                    });
                }
                syncCopy() {
                    if (this.syncing || this.clipboard.isEmpty())
                        return;
                    this.syncing = true;
                    let found = false;
                    for (let entry of this.entries) {
                        if (!entry.copyable)
                            continue;
                        let diEntry = entry.entry;
                        if (!diEntry) {
                            throw new Error("No display entry available.");
                        }
                        if (this.clipboard.contains(diEntry.eiTypeId, diEntry.pid)) {
                            entry.copied = true;
                            found = true;
                        }
                        else {
                            entry.copied = false;
                        }
                    }
                    this.clearClipboard = !found;
                    this.syncing = false;
                }
            }
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Relation;
        (function (Relation) {
            var cmd = Rocket.Cmd;
            var display = Rocket.Display;
            class ToOne {
                constructor(toOneSelector = null, embedded = null) {
                    this.toOneSelector = toOneSelector;
                    this.embedded = embedded;
                    if (toOneSelector && embedded) {
                        embedded.whenChanged(function () {
                            if (embedded.currentEntry || embedded.newEntry) {
                                toOneSelector.jQuery.hide();
                            }
                            else {
                                toOneSelector.jQuery.show();
                            }
                        });
                    }
                }
                static from(jqToOne, clipboard = null) {
                    let toOne = jqToOne.data("rocketImplToOne");
                    if (toOne instanceof ToOne) {
                        return toOne;
                    }
                    let toOneSelector = null;
                    let jqSelector = jqToOne.children(".rocket-impl-selector");
                    if (jqSelector.length > 0) {
                        toOneSelector = new ToOneSelector(jqSelector);
                    }
                    let jqCurrent = jqToOne.children(".rocket-impl-current");
                    let jqNew = jqToOne.children(".rocket-impl-new");
                    let jqDetail = jqToOne.children(".rocket-impl-detail");
                    let addControlFactory = null;
                    let toOneEmbedded = null;
                    if (jqCurrent.length > 0 || jqNew.length > 0 || jqDetail.length > 0) {
                        let newEntryFormUrl = jqNew.data("new-entry-form-url");
                        if (jqNew.length > 0 && newEntryFormUrl) {
                            let propertyPath = jqNew.data("property-path");
                            let entryFormRetriever = new Relation.EmbeddedEntryRetriever(jqNew.data("new-entry-form-url"), propertyPath, jqNew.data("draftMode"));
                            entryFormRetriever.grouped = !!jqToOne.data("grouped");
                            entryFormRetriever.sortable = false;
                            addControlFactory = new Relation.AddControlFactory(entryFormRetriever, jqNew.data("add-item-label"), jqNew.data("paste-item-label"));
                            let eiTypeIds = jqNew.data("ei-type-range");
                            if (clipboard && eiTypeIds) {
                                addControlFactory.pasteStrategy = {
                                    clipboard: clipboard,
                                    pastableEiTypeIds: eiTypeIds
                                };
                            }
                        }
                        toOneEmbedded = new ToOneEmbedded(jqToOne, addControlFactory, clipboard);
                        jqCurrent.children(".rocket-impl-entry").each(function () {
                            toOneEmbedded.currentEntry = new Relation.EmbeddedEntry($(this), toOneEmbedded.isReadOnly(), false, !!clipboard);
                        });
                        jqNew.children(".rocket-impl-entry").each(function () {
                            toOneEmbedded.newEntry = new Relation.EmbeddedEntry($(this), toOneEmbedded.isReadOnly(), false);
                        });
                        jqDetail.children(".rocket-impl-entry").each(function () {
                            toOneEmbedded.currentEntry = new Relation.EmbeddedEntry($(this), true, false, !!clipboard);
                        });
                    }
                    toOne = new ToOne(toOneSelector, toOneEmbedded);
                    jqToOne.data("rocketImplToOne", toOne);
                    return toOne;
                }
            }
            Relation.ToOne = ToOne;
            class ToOneEmbedded {
                constructor(jqToOne, addControlFactory = null, clipboard = null) {
                    this.clipboard = clipboard;
                    this.reduceEnabled = true;
                    this.expandZone = null;
                    this.changedCallbacks = new Array();
                    this.syncing = false;
                    this.jqToOne = jqToOne;
                    this.addControlFactory = addControlFactory;
                    this.reduceEnabled = (true == jqToOne.data("reduced"));
                    this.closeLabel = jqToOne.data("close-label");
                    this.jqEmbedded = $("<div />", {
                        "class": "rocket-impl-embedded"
                    });
                    this.jqToOne.append(this.jqEmbedded);
                    this.jqEntries = $("<div />");
                    this.jqEmbedded.append(this.jqEntries);
                    this.initClipboard();
                    this.changed();
                }
                isReadOnly() {
                    return this.addControlFactory === null;
                }
                changed() {
                    if (this.addControlFactory === null)
                        return;
                    if (!this.addControl) {
                        this.addControl = this.createAddControl();
                    }
                    if (this.currentEntry || this.newEntry) {
                        this.addControl.jQuery.hide();
                        if (this.addGroup) {
                            this.addGroup.hide();
                        }
                    }
                    else {
                        this.addControl.jQuery.show();
                        if (this.addGroup) {
                            this.addGroup.show();
                        }
                    }
                    this.triggerChanged();
                    Rocket.scan();
                }
                createAddControl() {
                    var addControl = this.addControlFactory.createAdd();
                    let jqAdd = addControl.jQuery;
                    this.jqEmbedded.append(jqAdd);
                    addControl.onNewEmbeddedEntry((newEntry) => {
                        this.newEntry = newEntry;
                        if (!this.isExpanded()) {
                            this.expand();
                        }
                    });
                    return addControl;
                }
                get currentEntry() {
                    return this._currentEntry;
                }
                set currentEntry(entry) {
                    if (this._currentEntry === entry)
                        return;
                    if (this._currentEntry) {
                        this._currentEntry.dispose();
                    }
                    this._currentEntry = entry;
                    if (!entry)
                        return;
                    if (this.newEntry) {
                        this._currentEntry.jQuery.detach();
                    }
                    entry.onRemove(() => {
                        this._currentEntry.dispose();
                        this._currentEntry = null;
                        this.changed();
                    });
                    this.initCopy(entry);
                    this.initEntry(entry);
                    this.changed();
                }
                get newEntry() {
                    return this._newEntry;
                }
                set newEntry(entry) {
                    if (this._newEntry === entry)
                        return;
                    if (this._newEntry) {
                        this._newEntry.dispose();
                    }
                    this._newEntry = entry;
                    if (!entry)
                        return;
                    if (this.currentEntry) {
                        this.currentEntry.jQuery.detach();
                    }
                    entry.onRemove(() => {
                        this._newEntry.dispose();
                        this._newEntry = null;
                        if (this.currentEntry) {
                            this.currentEntry.jQuery.appendTo(this.jqEntries);
                        }
                        this.changed();
                    });
                    this.initEntry(entry);
                    this.changed();
                }
                initEntry(entry) {
                    this.jqEntries.append(entry.jQuery);
                    if (this.isExpanded()) {
                        entry.expand(true);
                    }
                    else {
                        entry.reduce();
                    }
                    entry.onFocus(() => {
                        this.expand();
                    });
                }
                isExpanded() {
                    return this.expandZone !== null || !this.reduceEnabled;
                }
                expand() {
                    if (this.isExpanded())
                        return;
                    this.expandZone = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqToOne))
                        .createZone(window.location.href);
                    this.jqEmbedded.detach();
                    let contentJq = $("<div />", { "class": "rocket-content" }).append(this.jqEmbedded);
                    this.expandZone.applyContent(contentJq);
                    $("<header></header>").insertBefore(contentJq);
                    this.expandZone.layer.pushHistoryEntry(window.location.href);
                    if (this.newEntry) {
                        this.newEntry.expand(true);
                    }
                    if (this.currentEntry) {
                        this.currentEntry.expand(true);
                    }
                    var jqCommandButton = this.expandZone.menu.mainCommandList
                        .createJqCommandButton({ iconType: "fa fa-trash-o", label: this.closeLabel, severity: display.Severity.WARNING }, true);
                    jqCommandButton.click(() => {
                        this.expandZone.layer.close();
                    });
                    this.expandZone.on(cmd.Zone.EventType.CLOSE, () => {
                        this.reduce();
                    });
                    this.changed();
                }
                reduce() {
                    if (!this.isExpanded())
                        return;
                    this.expandZone = null;
                    this.jqEmbedded.detach();
                    this.jqToOne.append(this.jqEmbedded);
                    if (this.newEntry) {
                        this.newEntry.reduce();
                    }
                    if (this.currentEntry) {
                        this.currentEntry.reduce();
                    }
                    this.changed();
                }
                triggerChanged() {
                    for (let callback of this.changedCallbacks) {
                        callback();
                    }
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
                initCopy(entry) {
                    if (!this.clipboard || !entry.copyable)
                        return;
                    let diEntry = entry.entry;
                    if (!diEntry) {
                        throw new Error("No display entry available.");
                    }
                    entry.copied = this.clipboard.contains(diEntry.eiTypeId, diEntry.pid);
                    entry.onCopy(() => {
                        if (this.syncing)
                            return;
                        this.syncing = true;
                        if (!entry.copied) {
                            this.clipboard.remove(diEntry.eiTypeId, diEntry.pid);
                        }
                        else {
                            this.clipboard.clear();
                            this.clipboard.add(diEntry.eiTypeId, diEntry.pid, diEntry.identityString);
                        }
                        this.syncing = false;
                    });
                }
                initClipboard() {
                    if (!this.clipboard)
                        return;
                    let onChanged = () => {
                        this.syncCopy();
                    };
                    this.clipboard.onChanged(onChanged);
                    Rocket.Cmd.Zone.of(this.jqToOne).page.on("disposed", () => {
                        if (this.addControl) {
                            this.addControl.dispose();
                        }
                        if (this.addGroup) {
                            this.addGroup.jQuery.remove();
                        }
                        this.clipboard.offChanged(onChanged);
                    });
                }
                syncCopy() {
                    if (!this.currentEntry || !this.currentEntry.copyable)
                        return;
                    if (this.syncing)
                        return;
                    let diEntry = this.currentEntry.entry;
                    if (!diEntry) {
                        throw new Error("No display entry available.");
                    }
                    this.syncing = true;
                    if (this.clipboard.contains(diEntry.eiTypeId, diEntry.pid)) {
                        this.currentEntry.copied = true;
                    }
                    else {
                        this.currentEntry.copied = false;
                    }
                    this.syncing = false;
                }
            }
            class ToOneSelector {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    this.browserLayer = null;
                    this.browserSelectorObserver = null;
                    this.jqElem = jqElem;
                    this.jqInput = jqElem.children("input").hide();
                    this.originalPid = jqElem.data("original-ei-id");
                    this.identityStrings = jqElem.data("identity-strings");
                    this.init();
                    this.selectEntry(this.selectedPid);
                }
                get jQuery() {
                    return this.jqElem;
                }
                get selectedPid() {
                    let pid = this.jqInput.val().toString();
                    if (pid.length == 0)
                        return null;
                    return pid;
                }
                init() {
                    this.jqSelectedEntry = $("<div />");
                    this.jqSelectedEntry.append(this.jqEntryLabel = $("<span />", { "text": this.identityStrings[this.originalPid] }));
                    new display.CommandList($("<div />").appendTo(this.jqSelectedEntry), true)
                        .createJqCommandButton({ iconType: "fa fa-trash-o", label: this.jqElem.data("remove-entry-label") })
                        .click(() => {
                        this.clear();
                    });
                    this.jqElem.append(this.jqSelectedEntry);
                    var jqCommandList = $("<div />");
                    this.jqElem.append(jqCommandList);
                    var commandList = new display.CommandList(jqCommandList);
                    commandList.createJqCommandButton({ label: this.jqElem.data("select-label") })
                        .mouseenter(() => {
                        this.loadBrowser();
                    })
                        .click(() => {
                        this.openBrowser();
                    });
                    this.resetButtonJq = commandList.createJqCommandButton({ label: this.jqElem.data("reset-label") })
                        .click(() => {
                        this.reset();
                    }).hide();
                }
                selectEntry(pid, identityString = null) {
                    this.jqInput.val(pid);
                    if (pid === null) {
                        this.jqSelectedEntry.hide();
                        return;
                    }
                    this.jqSelectedEntry.show();
                    if (identityString === null) {
                        identityString = this.identityStrings[pid];
                    }
                    this.jqEntryLabel.text(identityString);
                    if (this.originalPid != this.selectedPid) {
                        this.resetButtonJq.show();
                    }
                    else {
                        this.resetButtonJq.hide();
                    }
                }
                reset() {
                    this.selectEntry(this.originalPid);
                }
                clear() {
                    this.selectEntry(null);
                }
                loadBrowser() {
                    if (this.browserLayer !== null)
                        return;
                    var that = this;
                    this.browserLayer = Rocket.getContainer().createLayer(cmd.Zone.of(this.jqElem));
                    this.browserLayer.hide();
                    this.browserLayer.on(cmd.Layer.EventType.CLOSE, function () {
                        that.browserLayer = null;
                        that.browserSelectorObserver = null;
                    });
                    let url = this.jqElem.data("overview-tools-url");
                    this.browserLayer.monitor.exec(url).then(() => {
                        let zone = this.browserLayer.getZoneByUrl(url);
                        that.iniBrowserPage(zone);
                        zone.on(Rocket.Cmd.Zone.EventType.CONTENT_CHANGED, () => {
                            this.iniBrowserPage(zone);
                        });
                    });
                }
                iniBrowserPage(zone) {
                    if (this.browserLayer === null)
                        return;
                    var ocs = Impl.Overview.OverviewPage.findAll(zone.jQuery);
                    if (ocs.length == 0)
                        return;
                    ocs[0].initSelector(this.browserSelectorObserver = new Rocket.Display.SingleEntrySelectorObserver());
                    zone.menu.zoneCommandsJq.find(".rocket-important").removeClass("rocket-important");
                    zone.menu.partialCommandList.createJqCommandButton({
                        label: this.jqElem.data("select-label"),
                        severity: Rocket.Display.Severity.PRIMARY,
                        important: true
                    }).click(() => {
                        this.updateSelection();
                        zone.layer.hide();
                    });
                    zone.menu.partialCommandList.createJqCommandButton({ label: this.jqElem.data("cancel-label") }).click(() => {
                        zone.layer.hide();
                    });
                    this.updateBrowser();
                }
                openBrowser() {
                    this.loadBrowser();
                    this.updateBrowser();
                    this.browserLayer.show();
                }
                updateBrowser() {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.browserSelectorObserver.setSelectedId(this.selectedPid);
                }
                updateSelection() {
                    if (this.browserSelectorObserver === null)
                        return;
                    this.clear();
                    this.browserSelectorObserver.getSelectedIds().forEach((id) => {
                        var identityString = this.browserSelectorObserver.getIdentityStringById(id);
                        if (identityString !== null) {
                            this.selectEntry(id, identityString);
                            return;
                        }
                        this.selectEntry(id);
                    });
                }
            }
        })(Relation = Impl.Relation || (Impl.Relation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        class LangState {
            constructor(activeLanguageIds) {
                this.listeners = [];
                this._activeLocaleIds = activeLanguageIds;
            }
            languageActive(localeId) {
                return !!this.activeLocaleIds.find((id) => id === localeId);
            }
            toggleActiveLocaleId(localeId, state) {
                if (!!state && !this.languageActive(localeId)) {
                    this.activeLocaleIds.push(localeId);
                }
                if (!state && !!this.languageActive(localeId)) {
                    this.activeLocaleIds.splice(this.activeLocaleIds.findIndex((id) => id === localeId), 1);
                }
                this.change(state);
            }
            onChanged(listener) {
                this.listeners.push(listener);
            }
            offChanged(listener) {
                this.listeners.splice(this.listeners.indexOf(listener), 1);
            }
            change(state) {
                this.listeners.forEach((listener) => {
                    listener.changed(state);
                });
            }
            get activeLocaleIds() {
                return this._activeLocaleIds;
            }
        }
        Impl.LangState = LangState;
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        class NavState {
            constructor(scrollPos, navGroupOpenedIds = []) {
                this.navStateListeners = [];
                this._scrollPos = scrollPos;
                this._navGroupOpenedIds = navGroupOpenedIds;
            }
            onChanged(elemJq, listener) {
                this.navStateListeners.push(listener);
                elemJq.on("remove", () => { this.offChanged(listener); });
            }
            offChanged(navStateListener) {
                this.navStateListeners.splice(this.navStateListeners.indexOf(navStateListener), 1);
            }
            change(id, opened) {
                if (opened) {
                    this.addOpenNavGroupId(id);
                }
                else {
                    this.removeOpenNavGroupId(id);
                }
                this.navStateListeners.forEach((navStateListener) => {
                    navStateListener.changed(opened);
                });
            }
            addOpenNavGroupId(id) {
                if (this._navGroupOpenedIds.indexOf(id) > -1)
                    return;
                this._navGroupOpenedIds.push(id);
            }
            removeOpenNavGroupId(id) {
                if (this._navGroupOpenedIds.indexOf(id) === -1)
                    return;
                this._navGroupOpenedIds.splice(this._navGroupOpenedIds.indexOf(id), 1);
            }
            isGroupOpen(navId) {
                return !!this._navGroupOpenedIds.find((id) => { return id == navId; });
            }
            get navGroupOpenedIds() {
                return this._navGroupOpenedIds;
            }
            get scrollPos() {
                return this._scrollPos;
            }
            set scrollPos(value) {
                this._scrollPos = value;
            }
        }
        Impl.NavState = NavState;
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        class UserStore {
            constructor(userId, navState, langState, userStoreItems) {
                this._userStoreItems = [];
                this._userId = userId;
                this._userStoreItems = userStoreItems;
                this._langState = langState;
                this._navState = navState;
            }
            static read(userId) {
                let userStoreUserItems;
                try {
                    userStoreUserItems = JSON.parse(window.localStorage.getItem(UserStore.STORAGE_ITEM_NAME)) || [];
                }
                catch (e) {
                    userStoreUserItems = [];
                }
                if (!(userStoreUserItems instanceof Array)) {
                    userStoreUserItems = [];
                }
                let userStoreItem = userStoreUserItems.find((userStoreUserItem) => {
                    return (userStoreUserItem.userId === userId);
                });
                if (!userStoreItem) {
                    return new UserStore(userId, new Impl.NavState(0, []), new Impl.LangState([]), userStoreUserItems);
                }
                return new UserStore(userId, new Impl.NavState(userStoreItem.scrollPos, userStoreItem.navGroupOpenedIds), new Impl.LangState(userStoreItem.activeLanguageLocaleIds), userStoreUserItems);
            }
            save() {
                var userItem = this._userStoreItems.find((userItem) => {
                    if (userItem.userId === this.userId) {
                        return true;
                    }
                });
                if (!userItem) {
                    userItem = { userId: this.userId,
                        scrollPos: this.navState.scrollPos,
                        navGroupOpenedIds: this.navState.navGroupOpenedIds,
                        activeLanguageLocaleIds: this.langState.activeLocaleIds };
                    this._userStoreItems.push(userItem);
                }
                userItem.scrollPos = this.navState.scrollPos;
                userItem.navGroupOpenedIds = this.navState.navGroupOpenedIds;
                userItem.activeLanguageLocaleIds = this.langState.activeLocaleIds;
                window.localStorage.setItem(UserStore.STORAGE_ITEM_NAME, JSON.stringify(this._userStoreItems));
            }
            get userId() {
                return this._userId;
            }
            get langState() {
                return this._langState;
            }
            get navState() {
                return this._navState;
            }
        }
        UserStore.STORAGE_ITEM_NAME = "rocket_user_state";
        Impl.UserStore = UserStore;
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Translation;
        (function (Translation) {
            class LoadJobExecuter {
                constructor() {
                    this.groups = [];
                }
                add(loadJob) {
                    for (let group of this.groups) {
                        if (group.add(loadJob))
                            return;
                    }
                    this.groups.push(LoadJobGroup.create(loadJob));
                }
                exec() {
                    for (let group of this.groups) {
                        group.exec();
                    }
                    this.groups = [];
                }
                static create(translatables) {
                    let lje = new LoadJobExecuter();
                    for (let translatable of translatables) {
                        for (let lj of translatable.loadJobs) {
                            lje.add(lj);
                        }
                    }
                    return lje;
                }
            }
            Translation.LoadJobExecuter = LoadJobExecuter;
            class LoadJobGroup {
                constructor(url) {
                    this.url = url;
                    this.loadJobs = [];
                }
                add(loadJob) {
                    if (!this.url.equals(loadJob.url)) {
                        return false;
                    }
                    this.loadJobs.push(loadJob);
                    return true;
                }
                exec() {
                    let guiFieldPaths = [];
                    for (let loadJob of this.loadJobs) {
                        guiFieldPaths.push(loadJob.guiFieldPath);
                        loadJob.content.loading = true;
                    }
                    let url = this.url.extR(null, { guiFieldPaths: guiFieldPaths });
                    Jhtml.lookupModel(url).then((result) => {
                        this.splitResult(result.model.snippet);
                    });
                }
                splitResult(snippet) {
                    let usedElements = [];
                    $(snippet.elements).children().each((i, elem) => {
                        let elemJq = $(elem);
                        let guiFieldPath = elemJq.data("rocket-impl-gui-field-path");
                        let loadJob = this.loadJobs.find(loadJob => loadJob.guiFieldPath == guiFieldPath);
                        let newContentJq = elemJq.children().first();
                        loadJob.content.replaceField(newContentJq);
                        loadJob.content.loading = false;
                        usedElements.push(newContentJq.get(0));
                    });
                    snippet.elements = usedElements;
                    snippet.markAttached();
                }
                static create(loadJob) {
                    let lj = new LoadJobGroup(loadJob.url);
                    lj.add(loadJob);
                    return lj;
                }
            }
        })(Translation = Impl.Translation || (Impl.Translation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Translation;
        (function (Translation) {
            class Translatable {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    this.srcGuiFieldPath = null;
                    this.loadUrlDefs = {};
                    this.copyUrlDefs = {};
                    this._contents = {};
                    let srcLoadConfig = jqElem.data("rocket-impl-src-load-config");
                    if (!srcLoadConfig)
                        return;
                    this.srcGuiFieldPath = srcLoadConfig.guiFieldPath;
                    for (let localeId in srcLoadConfig.loadUrlDefs) {
                        this.loadUrlDefs[localeId] = {
                            label: srcLoadConfig.loadUrlDefs[localeId].label,
                            url: Jhtml.Url.create(srcLoadConfig.loadUrlDefs[localeId].url)
                        };
                    }
                    for (let localeId in srcLoadConfig.copyUrlDefs) {
                        this.copyUrlDefs[localeId] = {
                            label: srcLoadConfig.copyUrlDefs[localeId].label,
                            url: Jhtml.Url.create(srcLoadConfig.copyUrlDefs[localeId].url)
                        };
                    }
                }
                get jQuery() {
                    return this.jqElem;
                }
                get localeIds() {
                    return Object.keys(this._contents);
                }
                get contents() {
                    let O = Object;
                    return O.values(this._contents);
                }
                set visibleLocaleIds(localeIds) {
                    for (let content of this.contents) {
                        content.visible = -1 < localeIds.indexOf(content.localeId);
                    }
                }
                get visibleLocaleIds() {
                    let localeIds = new Array();
                    for (let content of this.contents) {
                        if (!content.visible)
                            continue;
                        localeIds.push(content.localeId);
                    }
                    return localeIds;
                }
                set labelVisible(labelVisible) {
                    for (let content of this.contents) {
                        content.labelVisible = labelVisible;
                    }
                }
                set activeLocaleIds(localeIds) {
                    for (let content of this.contents) {
                        content.active = -1 < localeIds.indexOf(content.localeId);
                    }
                }
                get activeLocaleIds() {
                    let localeIds = new Array();
                    for (let content of this.contents) {
                        if (!content.active)
                            continue;
                        localeIds.push(content.localeId);
                    }
                    return localeIds;
                }
                get loadJobs() {
                    if (!this.srcGuiFieldPath)
                        return [];
                    let loadJobs = [];
                    for (let content of this.contents) {
                        if (content.loaded || content.loading || !content.visible || !content.active
                            || !this.loadUrlDefs[content.localeId]) {
                            continue;
                        }
                        loadJobs.push({
                            url: this.loadUrlDefs[content.localeId].url.extR(null, { "propertyPath": content.propertyPath }),
                            guiFieldPath: this.srcGuiFieldPath,
                            content: content
                        });
                    }
                    return loadJobs;
                }
                scan() {
                    this.jqElem.children().each((i, elem) => {
                        let jqElem = $(elem);
                        let localeId = jqElem.data("rocket-impl-locale-id");
                        if (!localeId || this._contents[localeId])
                            return;
                        let tc = this._contents[localeId] = new TranslatedContent(localeId, jqElem);
                        tc.drawCopyControl(this.copyUrlDefs, this.srcGuiFieldPath);
                    });
                }
                static test(elemJq) {
                    let translatable = elemJq.data("rocketImplTranslatable");
                    if (translatable instanceof Translatable) {
                        return translatable;
                    }
                    return null;
                }
                static from(jqElem) {
                    let translatable = Translatable.test(jqElem);
                    if (translatable instanceof Translatable) {
                        return translatable;
                    }
                    translatable = new Translatable(jqElem);
                    jqElem.data("rocketImplTranslatable", translatable);
                    translatable.scan();
                    return translatable;
                }
            }
            Translation.Translatable = Translatable;
            class TranslatedContent {
                constructor(_localeId, elemJq) {
                    this._localeId = _localeId;
                    this.elemJq = elemJq;
                    this.jqEnabler = null;
                    this.copyControlJq = null;
                    this.changedCallbacks = [];
                    this._visible = true;
                    this._labelVisible = true;
                    Rocket.Display.StructureElement.from(elemJq, true);
                    this._propertyPath = elemJq.data("rocket-impl-property-path");
                    this._pid = elemJq.data("rocket-impl-ei-id") || null;
                    this._fieldJq = elemJq.children();
                    this.elemJq.hide();
                    this._visible = false;
                }
                get loaded() {
                    return this.elemJq.children("div").children("div")
                        .children("input[type=hidden].rocket-impl-unloaded").length == 0;
                }
                get jQuery() {
                    return this.elemJq;
                }
                get fieldJq() {
                    return this._fieldJq;
                }
                replaceField(newFieldJq) {
                    this._fieldJq.replaceWith(newFieldJq);
                    this._fieldJq = newFieldJq;
                    this.updateLabelVisiblity();
                }
                get localeId() {
                    return this._localeId;
                }
                get propertyPath() {
                    return this._propertyPath;
                }
                get pid() {
                    return this._pid;
                }
                findLabelJq() {
                    return this.elemJq.find("label:first");
                }
                get prettyLocaleId() {
                    return this.findLabelJq().text();
                }
                get localeName() {
                    return this.findLabelJq().attr("title");
                }
                get visible() {
                    return this._visible;
                }
                set visible(visible) {
                    if (visible) {
                        if (this._visible)
                            return;
                        this._visible = true;
                        this.elemJq.show();
                        this.triggerChanged();
                        return;
                    }
                    if (!this._visible)
                        return;
                    this._visible = false;
                    this.elemJq.hide();
                    this.triggerChanged();
                }
                set labelVisible(labelVisible) {
                    if (this._labelVisible == labelVisible)
                        return;
                    this._labelVisible = labelVisible;
                    this.updateLabelVisiblity();
                }
                updateLabelVisiblity() {
                    if (this._labelVisible) {
                        this.findLabelJq().show();
                    }
                    else {
                        this.findLabelJq().hide();
                    }
                }
                get active() {
                    return this.jqEnabler ? false : true;
                }
                set active(active) {
                    if (active) {
                        if (this.jqEnabler) {
                            this.jqEnabler.remove();
                            this.jqEnabler = null;
                            this.triggerChanged();
                        }
                        if (this.copyControlJq) {
                            this.copyControlJq.show();
                        }
                        this.elemJq.removeClass("rocket-inactive");
                        return;
                    }
                    if (!this.jqEnabler) {
                        this.jqEnabler = $("<button />", {
                            "class": "rocket-impl-enabler",
                            "type": "button",
                            "text": " " + this.elemJq.data("rocket-impl-activate-label"),
                            "click": () => { this.active = true; }
                        }).prepend($("<i />", { "class": "fa fa-language", "text": "" })).appendTo(this.elemJq);
                        this.triggerChanged();
                    }
                    if (this.copyControlJq) {
                        this.copyControlJq.show();
                    }
                    this.elemJq.addClass("rocket-inactive");
                }
                drawCopyControl(copyUrlDefs, guiFieldPath) {
                    for (let localeId in copyUrlDefs) {
                        if (localeId == this.localeId)
                            continue;
                        if (!this.copyControl) {
                            this.copyControl = new CopyControl(this, guiFieldPath);
                            this.copyControl.draw(this.elemJq.data("rocket-impl-copy-tooltip"));
                        }
                        this.copyControl.addUrlDef(copyUrlDefs[localeId]);
                    }
                }
                get loading() {
                    return !!this.loaderJq;
                }
                set loading(loading) {
                    if (!loading) {
                        if (!this.loaderJq)
                            return;
                        this.loaderJq.remove();
                        this.loaderJq = null;
                        return;
                    }
                    if (this.loaderJq)
                        return;
                    this.loaderJq = $("<div />", {
                        class: "rocket-load-blocker"
                    }).append($("<div></div>", { class: "rocket-loader" })).appendTo(this.elemJq);
                }
                triggerChanged() {
                    for (let callback of this.changedCallbacks) {
                        callback();
                    }
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
            }
            Translation.TranslatedContent = TranslatedContent;
            class CopyControl {
                constructor(translatedContent, guiFieldPath) {
                    this.translatedContent = translatedContent;
                    this.guiFieldPath = guiFieldPath;
                }
                draw(tooltip) {
                    this.elemJq = $("<div></div>", { class: "rocket-impl-translation-copy-control rocket-simple-commands" });
                    this.translatedContent.jQuery.append(this.elemJq);
                    let buttonJq = $("<button />", { "type": "button", "class": "btn btn-secondary" })
                        .append($("<i></i>", { class: "fa fa-copy", title: tooltip }));
                    let menuJq = $("<div />", { class: "rocket-impl-translation-copy-menu" })
                        .append(this.menuUlJq = $("<ul></ul>"))
                        .append($("<div />", { class: "rocket-impl-tooltip", text: tooltip }));
                    this.toggler = Rocket.Display.Toggler.simple(buttonJq, menuJq);
                    this.elemJq.append(buttonJq);
                    this.elemJq.append(menuJq);
                }
                addUrlDef(urlDef) {
                    let url = this.completeCopyUrl(urlDef.url);
                    this.menuUlJq.append($("<li/>").append($("<a />", {
                        "text": urlDef.label
                    }).append($("<i></i>", { class: "fa fa-mail-forward" })).click((e) => {
                        e.stopPropagation();
                        this.copy(url);
                        this.toggler.close();
                    })));
                }
                completeCopyUrl(url) {
                    return url.extR(null, {
                        propertyPath: this.translatedContent.propertyPath,
                        toN2nLocale: this.translatedContent.localeId,
                        toPid: this.translatedContent.pid
                    });
                }
                copy(url) {
                    if (this.translatedContent.loading)
                        return;
                    let lje = new Translation.LoadJobExecuter();
                    lje.add({
                        content: this.translatedContent,
                        guiFieldPath: this.guiFieldPath,
                        url: url
                    });
                    lje.exec();
                }
                replace(snippet) {
                }
            }
        })(Translation = Impl.Translation || (Impl.Translation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Translation;
        (function (Translation) {
            class TranslationManager {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    this.min = 0;
                    this.translatables = [];
                    this.menuItems = [];
                    this.buttonJq = null;
                    this.changing = false;
                    this.min = parseInt(jqElem.data("rocket-impl-min"));
                    Rocket.Display.Toggler.simple(this.initControl(), this.initMenu());
                }
                val(visibleLocaleIds = []) {
                    let activeLocaleIds = [];
                    for (let menuItem of this.menuItems) {
                        if (!menuItem.active)
                            continue;
                        activeLocaleIds.push(menuItem.localeId);
                    }
                    let activeDisabled = activeLocaleIds.length <= this.min;
                    for (let menuItem of this.menuItems) {
                        if (activeLocaleIds.length >= this.min)
                            break;
                        if (menuItem.mandatory || menuItem.active
                            || visibleLocaleIds.indexOf(menuItem.localeId) == -1) {
                            continue;
                        }
                        menuItem.active = true;
                        activeLocaleIds.push(menuItem.localeId);
                    }
                    for (let menuItem of this.menuItems) {
                        if (menuItem.mandatory)
                            continue;
                        if (!menuItem.active && activeLocaleIds.length < this.min) {
                            menuItem.active = true;
                            activeLocaleIds.push(menuItem.localeId);
                        }
                        menuItem.disabled = activeDisabled && menuItem.active;
                    }
                    return activeLocaleIds;
                }
                registerTranslatable(translatable) {
                    if (-1 < this.translatables.indexOf(translatable))
                        return;
                    this.translatables.push(translatable);
                    translatable.activeLocaleIds = this.activeLocaleIds;
                    translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));
                    for (let tc of translatable.contents) {
                        tc.whenChanged(() => {
                            this.activeLocaleIds = translatable.activeLocaleIds;
                        });
                    }
                }
                unregisterTranslatable(translatable) {
                    let i = this.translatables.indexOf(translatable);
                    if (i > -1) {
                        this.translatables.splice(i, 1);
                    }
                }
                get activeLocaleIds() {
                    let localeIds = Array();
                    for (let menuItem of this.menuItems) {
                        if (menuItem.active) {
                            localeIds.push(menuItem.localeId);
                        }
                    }
                    return localeIds;
                }
                set activeLocaleIds(localeIds) {
                    if (this.changing)
                        return;
                    this.changing = true;
                    let changed = false;
                    for (let menuItem of this.menuItems) {
                        if (menuItem.mandatory)
                            continue;
                        let active = -1 < localeIds.indexOf(menuItem.localeId);
                        if (menuItem.active != active) {
                            changed = true;
                        }
                        menuItem.active = active;
                    }
                    if (!changed) {
                        this.changing = false;
                        return;
                    }
                    localeIds = this.val();
                    for (let translatable of this.translatables) {
                        translatable.activeLocaleIds = localeIds;
                    }
                    this.checkLoadJobs();
                    this.changing = false;
                }
                menuChanged() {
                    if (this.changing)
                        return;
                    this.changing = true;
                    let localeIds = this.val();
                    for (let translatable of this.translatables) {
                        translatable.activeLocaleIds = localeIds;
                    }
                    this.changing = false;
                }
                checkLoadJobs() {
                    Translation.LoadJobExecuter.create(this.translatables).exec();
                }
                initControl() {
                    let jqLabel = this.jqElem.children("label:first");
                    let cmdList = Rocket.Display.CommandList.create(true);
                    let buttonJq = cmdList.createJqCommandButton({
                        iconType: "fa fa-language",
                        label: jqLabel.text(),
                        tooltip: this.jqElem.find("rocket-impl-tooltip").text()
                    });
                    jqLabel.replaceWith(cmdList.jQuery);
                    return buttonJq;
                }
                initMenu() {
                    let menuJq = this.jqElem.find(".rocket-impl-translation-menu");
                    menuJq.hide();
                    menuJq.find("li").each((i, elem) => {
                        let mi = new MenuItem($(elem));
                        this.menuItems.push(mi);
                        mi.whenChanged(() => {
                            this.menuChanged();
                        });
                    });
                    return menuJq;
                }
                static from(jqElem) {
                    let tm = jqElem.data("rocketImplTranslationManager");
                    if (tm instanceof TranslationManager) {
                        return tm;
                    }
                    tm = new TranslationManager(jqElem);
                    jqElem.data("rocketImplTranslationManager", tm);
                    return tm;
                }
            }
            Translation.TranslationManager = TranslationManager;
            class MenuItem {
                constructor(jqElem) {
                    this.jqElem = jqElem;
                    this._disabled = false;
                    this._localeId = this.jqElem.data("rocket-impl-locale-id");
                    this._mandatory = this.jqElem.data("rocket-impl-mandatory") ? true : false;
                    this.init();
                }
                init() {
                    if (this.jqCheck) {
                        throw new Error("already initialized");
                    }
                    this.jqCheck = this.jqElem.find("input[type=checkbox]");
                    if (this.mandatory) {
                        this.jqCheck.prop("checked", true);
                        this.jqCheck.prop("disabled", true);
                        this.disabled = true;
                    }
                    this.jqCheck.change(() => { this.updateClasses(); });
                }
                updateClasses() {
                    if (this.disabled) {
                        this.jqElem.addClass("rocket-disabled");
                    }
                    else {
                        this.jqElem.removeClass("rocket-disabled");
                    }
                    if (this.active) {
                        this.jqElem.addClass("rocket-active");
                    }
                    else {
                        this.jqElem.removeClass("rocket-active");
                    }
                }
                whenChanged(callback) {
                    this.jqCheck.change(callback);
                }
                get disabled() {
                    return this.jqCheck.is(":disabled") || this._disabled;
                }
                set disabled(disabled) {
                    this._disabled = disabled;
                    this.updateClasses();
                }
                get active() {
                    return this.jqCheck.is(":checked");
                }
                set active(active) {
                    this.jqCheck.prop("checked", active);
                    this.updateClasses();
                }
                get localeId() {
                    return this._localeId;
                }
                get mandatory() {
                    return this._mandatory;
                }
            }
        })(Translation = Impl.Translation || (Impl.Translation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Translation;
        (function (Translation) {
            class Translator {
                constructor(container, userStore) {
                    this.container = container;
                    this.userStore = userStore;
                }
                scan() {
                    for (let context of this.container.getAllZones()) {
                        let elems = context.jQuery.find(".rocket-impl-translation-manager").toArray();
                        let elem;
                        while (elem = elems.pop()) {
                            this.initTm($(elem), context);
                        }
                        let jqViewControl = context.menu.toolbar.getJqControls().find(".rocket-impl-translation-view-control");
                        let jqTranslatables = context.jQuery.find(".rocket-impl-translatable");
                        if (jqTranslatables.length == 0) {
                            jqViewControl.hide();
                            continue;
                        }
                        jqViewControl.show();
                        let isInitViewMenu = false;
                        if (jqViewControl.length == 0) {
                            jqViewControl = $("<div />", { "class": "rocket-impl-translation-view-control" });
                            context.menu.toolbar.getJqControls().show().append(jqViewControl);
                            isInitViewMenu = true;
                        }
                        let viewMenu = Translation.ViewMenu.from(jqViewControl);
                        jqTranslatables.each((i, elem) => {
                            viewMenu.registerTranslatable(Translation.Translatable.from($(elem)));
                        });
                        if (isInitViewMenu) {
                            this.initViewMenu(viewMenu);
                        }
                        viewMenu.checkLoadJobs();
                    }
                }
                initTm(jqElem, context) {
                    let tm = Translation.TranslationManager.from(jqElem);
                    tm.val(this.userStore.langState.activeLocaleIds);
                    let se = Rocket.Display.StructureElement.of(jqElem);
                    let jqBase = null;
                    if (!se) {
                        jqBase = context.jQuery;
                    }
                    else {
                        jqBase = se.jQuery;
                    }
                    jqBase.find(".rocket-impl-translatable-" + jqElem.data("rocket-impl-mark-class-key")).each((i, elem) => {
                        let elemJq = $(elem);
                        if (Translation.Translatable.test(elemJq)) {
                            return;
                        }
                        tm.registerTranslatable(Translation.Translatable.from(elemJq));
                    });
                }
                ensureSomethingOn(viewMenuItems) {
                    for (let localeId in viewMenuItems) {
                        if (viewMenuItems[localeId].on) {
                            return;
                        }
                    }
                    for (let localeId in viewMenuItems) {
                        viewMenuItems[localeId].on = true;
                    }
                }
                initViewMenu(viewMenu) {
                    let langState = this.userStore.langState;
                    let viewMenuItems = viewMenu.items;
                    let listeners = [];
                    if (this.userStore.langState.activeLocaleIds.length > 0) {
                        for (let localeId in viewMenuItems) {
                            viewMenuItems[localeId].on = this.userStore.langState.languageActive(localeId);
                        }
                    }
                    this.ensureSomethingOn(viewMenuItems);
                    for (let localeId in viewMenuItems) {
                        let viewMenuItem = viewMenuItems[localeId];
                        this.userStore.langState.toggleActiveLocaleId(localeId, viewMenuItem.on);
                        viewMenuItem.whenChanged(() => {
                            this.userStore.langState.toggleActiveLocaleId(localeId, viewMenuItem.on);
                            this.userStore.save();
                        });
                        listeners.push({
                            changed(state) {
                                if (langState.languageActive(localeId) === viewMenuItem.on)
                                    return;
                                viewMenuItem.on = state;
                            }
                        });
                        this.userStore.langState.onChanged(listeners[listeners.length - 1]);
                    }
                    let observer = new MutationObserver((mutations) => {
                        if (!viewMenu.jQuery.is(":visible")) {
                            listeners.forEach((listener) => {
                                this.userStore.langState.offChanged(listener);
                            });
                            observer.disconnect();
                            return;
                        }
                    });
                    observer.observe($(".rocket-main-layer").get(0), { childList: true, attributes: true, characterData: true, subtree: true });
                }
            }
            Translation.Translator = Translator;
        })(Translation = Impl.Translation || (Impl.Translation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
var Rocket;
(function (Rocket) {
    var Impl;
    (function (Impl) {
        var Translation;
        (function (Translation) {
            class ViewMenu {
                constructor(jqContainer) {
                    this.jqContainer = jqContainer;
                    this.translatables = [];
                    this._items = {};
                    this.changing = false;
                }
                get jQuery() {
                    return this.jqContainer;
                }
                get items() {
                    return this._items;
                }
                get numItems() {
                    return Object.keys(this._items).length;
                }
                draw(languagesLabel, visibleLabel, tooltip) {
                    $("<div />", { "class": "rocket-impl-translation-status" })
                        .append($("<label />", { "text": visibleLabel }).prepend($("<i></i>", { "class": "fa fa-language" })))
                        .append(this.jqStatus = $("<span></span>"))
                        .prependTo(this.jqContainer);
                    let buttonJq = new Rocket.Display.CommandList(this.jqContainer)
                        .createJqCommandButton({
                        iconType: "fa fa-cog",
                        label: languagesLabel,
                        tooltip: tooltip
                    });
                    let menuJq = $("<div />", { "class": "rocket-impl-translation-status-menu" })
                        .append(this.menuUlJq = $("<ul></ul>"))
                        .append($("<div />", { "class": "rocket-impl-tooltip", "text": tooltip }))
                        .hide();
                    Rocket.Display.Toggler.simple(buttonJq, menuJq);
                    this.jqContainer.append(menuJq);
                }
                updateStatus() {
                    let prettyLocaleIds = [];
                    for (let localeId in this._items) {
                        if (!this._items[localeId].on)
                            continue;
                        prettyLocaleIds.push(this._items[localeId].prettyLocaleId);
                    }
                    this.jqStatus.empty();
                    this.jqStatus.text(prettyLocaleIds.join(", "));
                    let onDisabled = prettyLocaleIds.length == 1;
                    for (let localeId in this._items) {
                        this._items[localeId].disabled = onDisabled && this._items[localeId].on;
                    }
                }
                get visibleLocaleIds() {
                    let localeIds = [];
                    for (let localeId in this._items) {
                        if (!this._items[localeId].on)
                            continue;
                        localeIds.push(localeId);
                    }
                    return localeIds;
                }
                registerTranslatable(translatable) {
                    if (-1 < this.translatables.indexOf(translatable))
                        return;
                    if (!this.jqStatus) {
                        this.draw(translatable.jQuery.data("rocket-impl-languages-label"), translatable.jQuery.data("rocket-impl-visible-label"), translatable.jQuery.data("rocket-impl-languages-view-tooltip"));
                    }
                    this.translatables.push(translatable);
                    translatable.jQuery.on("remove", () => this.unregisterTranslatable(translatable));
                    let labelVisible = this.numItems > 1;
                    for (let content of translatable.contents) {
                        if (!this._items[content.localeId]) {
                            let item = this._items[content.localeId] = new ViewMenuItem(content.localeId, content.localeName, content.prettyLocaleId);
                            item.draw($("<li />").appendTo(this.menuUlJq));
                            item.on = this.numItems == 1;
                            item.whenChanged(() => this.menuChanged());
                            this.updateStatus();
                        }
                        content.visible = this._items[content.localeId].on;
                        content.labelVisible = labelVisible;
                        content.whenChanged(() => {
                            if (this.changing || !content.active)
                                return;
                            this._items[content.localeId].on = true;
                        });
                    }
                }
                getNumOn() {
                    let num = 0;
                    for (let localeId in this._items) {
                        if (this._items[localeId].on) {
                            num++;
                        }
                    }
                    return num;
                }
                unregisterTranslatable(translatable) {
                    let i = this.translatables.indexOf(translatable);
                    if (-1 < i) {
                        this.translatables.splice(i, 1);
                    }
                }
                checkLoadJobs() {
                    Translation.LoadJobExecuter.create(this.translatables).exec();
                }
                menuChanged() {
                    if (this.changing) {
                        throw new Error("already changing");
                    }
                    this.changing = true;
                    let visiableLocaleIds = [];
                    for (let i in this._items) {
                        if (this._items[i].on) {
                            visiableLocaleIds.push(this._items[i].localeId);
                        }
                    }
                    let labelVisible = this.numItems > 1;
                    for (let translatable of this.translatables) {
                        translatable.visibleLocaleIds = visiableLocaleIds;
                        translatable.labelVisible = labelVisible;
                    }
                    this.updateStatus();
                    this.checkLoadJobs();
                    this.changing = false;
                }
                static from(jqElem) {
                    let vm = jqElem.data("rocketImplViewMenu");
                    if (vm instanceof ViewMenu) {
                        return vm;
                    }
                    vm = new ViewMenu(jqElem);
                    jqElem.data("rocketImplViewMenu", vm);
                    return vm;
                }
            }
            Translation.ViewMenu = ViewMenu;
            class ViewMenuItem {
                constructor(localeId, label, prettyLocaleId) {
                    this.localeId = localeId;
                    this.label = label;
                    this.prettyLocaleId = prettyLocaleId;
                    this._on = true;
                    this.changedCallbacks = [];
                }
                draw(jqElem) {
                    this.jqI = $("<i></i>");
                    this.jqA = $("<a />", { "href": "", "text": this.label + " ", "class": "btn" })
                        .append(this.jqI)
                        .appendTo(jqElem)
                        .on("click", (evt) => {
                        if (this.disabled)
                            return;
                        this.on = !this.on;
                        evt.preventDefault();
                        return false;
                    });
                    this.checkI();
                }
                get disabled() {
                    return this.jqA.hasClass("disabled");
                }
                set disabled(disabled) {
                    if (disabled) {
                        this.jqA.addClass("disabled");
                    }
                    else {
                        this.jqA.removeClass("disabled");
                    }
                }
                get on() {
                    return this._on;
                }
                set on(on) {
                    if (this._on == on)
                        return;
                    this._on = on;
                    this.checkI();
                    this.triggerChanged();
                }
                triggerChanged() {
                    for (let callback of this.changedCallbacks) {
                        callback();
                    }
                }
                whenChanged(callback) {
                    this.changedCallbacks.push(callback);
                }
                checkI() {
                    if (this.on) {
                        this.jqA.addClass("rocket-active");
                        this.jqI.attr("class", "fa fa-toggle-on");
                    }
                    else {
                        this.jqA.removeClass("rocket-active");
                        this.jqI.attr("class", "fa fa-toggle-off");
                    }
                }
            }
            Translation.ViewMenuItem = ViewMenuItem;
        })(Translation = Impl.Translation || (Impl.Translation = {}));
    })(Impl = Rocket.Impl || (Rocket.Impl = {}));
})(Rocket || (Rocket = {}));
//# sourceMappingURL=rocket.js.map