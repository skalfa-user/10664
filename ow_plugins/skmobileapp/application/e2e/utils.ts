import { browser, element, by, protractor, ElementArrayFinder } from 'protractor';
import { promise as webdriverPromise } from 'selenium-webdriver';

export const APPLICATION_CONFIG = require('../application.config.json');

export class Utils {
    authToken: string = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpZCI6MTkxMDYyLCJuYW1lIjoidGVzdGVyIiwiZW1haWwiOiJ0ZXN0ZXJAbWFpbC5jb20iLCJleHAiOjE2MzE1MjM3NjF9.NUw7G7N8AyVTFvjL64af_A4gUwlRgfbtDCfabKW2hlA';

    /**
     * Reload app
     */
    async reloadApp(initialFixtures: Array<string> = [], isUserLogged: boolean = false, isServerEventsInitialOnly: boolean = true): Promise<any> {
        browser.ignoreSynchronization = false;

        let requestParams = '?fixtures=' + (initialFixtures.length ? initialFixtures.join(',') : '') + '&disable_push=true';

        // server events initial only
        if (isServerEventsInitialOnly) {
            requestParams += '&se_init_only=true';
        }

        await browser.get(requestParams);

        if (isUserLogged) {
            this.setValueToLocalStore('token', this.authToken);

            await this.reloadApp(initialFixtures);
        }

        const waitAngular = browser.waitForAngular();

        waitAngular.then(() => {
            browser.ignoreSynchronization = true;
        });

        return waitAngular;
    }

    /**
     * Init app geo location
     */
    initAppGeoLocation(latitude: number = 100, longitude: number = 100): void {
        browser.executeScript(`window.navigator.geolocation.getCurrentPosition = function(success, error) {
            success({
                coords : {
                    latitude: ${latitude},
                    longitude: ${longitude}
                }
            });
        };`);
    }

    /**
     * Reload app fixtures
     */
    reloadAppFixtures(fixtures: Array<string>): void {
        this.setValueToLocalStore('fixtures', fixtures.join(','));
    }

    /**
     * Get page title
     */
    getPageTitle(): webdriverPromise.Promise<string> {
        return browser.getTitle();
    }

    /**
     * Clean browser
     */
    cleanBrowser(): void {
        browser.manage().deleteAllCookies();
        browser.executeScript('window.sessionStorage.clear();');
        browser.executeScript('window.localStorage.clear();');
    }

    /**
     * Fill google location box by id
     */
    async fillGoogleLocationBoxById(id, location: string, timeOut: number = 500): Promise<any> {
        // show a select box
        await element.all(by.id(id)).first().element(by.tagName('input')).click();

        browser.sleep(timeOut);

        await this.fillInputByPlaceholder('Search', location);

        // wait for results
        await this.waitForElement('sk-autocomplete-result');

        // click a first item
        element(by.className('sk-autocomplete-results'))
            .all(by.className('sk-autocomplete-result')).get(0).click();

        return browser.sleep(timeOut);
    }

    /**
     * Fill date box by id
     */
    async fillDateBoxById(id, monthIndex: number, dayIndex: number, yearIndex: number, timeOut: number = 500): Promise<any> {
        // show a select box
        await element.all(by.id(id)).first().element(by.tagName('button')).click();

        browser.sleep(timeOut);

        // select a month
        element(by.className('picker-columns'))
            .all(by.className('picker-col')).get(0)
            .all(by.className('picker-opt')).each(function(element, index) {
                if (index <= monthIndex) {
                    element.click();
                    browser.sleep(50);
                }
            });

        // select a day
        element(by.className('picker-columns'))
            .all(by.className('picker-col')).get(1)
            .all(by.className('picker-opt')).each(function(element, index) {
                if (index <= dayIndex) {
                    element.click();
                    browser.sleep(50);
                }
        });

        // select a year
        element(by.className('picker-columns'))
            .all(by.className('picker-col')).get(2)
            .all(by.className('picker-opt')).each(function(element, index) {
                if (index <= yearIndex) {
                    element.click();
                    browser.sleep(50);
                }
            });

        // click the 'ok' button
        element.all(by.className('picker-toolbar-button')).last().click();

        return browser.sleep(timeOut);
    }

    /**
     * Click action menu item
     */
    async clickActionMenuItem(actionIndex: number, timeOut: number = 500): Promise<any> {
        // wait before the action menu is active
        await this.waitForElement('action-sheet-wrapper');

        // give a chance to finish all animations
        await browser.sleep(timeOut);

        await element(by.className('action-sheet-wrapper'))
            .all(by.className('action-sheet-button')).get(actionIndex).click();

        return  browser.sleep(timeOut);
    }

    /**
     * Fill select box by id
     */
    async fillSelectBoxById(id, selectedIndexes: Array<number>, timeOut: number = 500): Promise<any> {
        // show a select box
        await element.all(by.id(id)).first().element(by.tagName('button')).click();

        browser.sleep(timeOut);

        // select a value
        element.all(by.className('alert-tappable')).each(function(element, index) {
            if (selectedIndexes.indexOf(index) != -1) {
                element.click();
            }
        });

        // click the 'ok' button
        element.all(by.className('alert-button')).last().click();

        return browser.sleep(timeOut);
    }

    /**
     * Upload file from static
     */
    async uploadFileFromStatic(fileName: string, uploaderCssClass, timeOut: number = 500): Promise<any> {
        const path = require('path');
        const fileToUpload = '../e2e/server/static/' + fileName,
        absolutePath = path.resolve(__dirname, fileToUpload);

        await element(by.css('.' + uploaderCssClass + ' .sk-file-uploader-input')).sendKeys(absolutePath);

        return browser.sleep(timeOut);
    }

    /**
     * Click
     */
    async click(cssClass, index: number = 0, timeOut: number = 500): Promise<any> {
        await element.all(by.className(cssClass)).get(index).click();

        return browser.sleep(timeOut);
    }

    /**
     * Long tap
     */
    async longTap(cssClass, timeOut: number = 300): Promise<any> {
        await browser.actions().mouseDown(element(by.className(cssClass))).perform();
        await browser.sleep(timeOut);

        return browser.actions().mouseUp().perform();
    }

    /**
     * Fill input by placeholder
     */
    async fillInputByPlaceholder(placeholder: string, value: string, isClear: boolean = false, timeOut: number = 500): Promise<any> {
        const input = element(by.css('[placeholder="' + placeholder + '"]'));

        if (isClear) {
            const text: string = await input.getAttribute('value');
            const textLength: number = text.length
            const backspaceSeries = Array(textLength + 1).join(protractor.Key.BACK_SPACE);

            await input.sendKeys(backspaceSeries);
        }

        return input.sendKeys(value);
    }

    /**
     * Fill input by css class
     */
    async fillInputByCssClass(cssClass: string, value: string, isClear: boolean = false, timeOut: number = 500): Promise<any> {
        const input = element(by.className(cssClass));

        if (isClear) {
            const text: string = await input.getAttribute('value');
            const textLength: number = text.length
            const backspaceSeries = Array(textLength + 1).join(protractor.Key.BACK_SPACE);

            await input.sendKeys(backspaceSeries);
        }

        return input.sendKeys(value);
    }

    /**
     * Toaster
     */
    async toaster(): Promise<string> {
        await this.waitForElement('toast-message');

        return element(by.className('toast-message')).getText();
    }

    /**
     * Alert
     */
    async alert(): Promise<string> {
        await this.waitForElement('alert-sub-title');

        return browser.findElement(by.className('alert-sub-title')).getText();
    }

    /**
     * Alert title
     */
    async alertTitle(): Promise<string> {
        await this.waitForElement('alert-head');

        return browser.findElement(by.className('alert-head')).getText();
    }

    /**
     * Confirm alert
     */
    async confirmAlert(timeOut = 500): Promise<any> {
        await this.waitForElement('alert-button-group');

        // give a chance to finish all animations
        await browser.sleep(timeOut);

        await element(by.
            className('alert-button-group')).all(by.className('alert-button')).get(1).click();

        return browser.sleep(timeOut);
    }

    /**
     * Find element by text
     */
    findElementByText(cssClass: string, text: string): ElementArrayFinder {
        return element.all(by.cssContainingText(cssClass, text));
    }

    /**
     * Wait for element
     */
    waitForElement(cssClass: string, timeOut: number = 5000): webdriverPromise.Promise<any>  {
        const EC = protractor.ExpectedConditions;

        return browser.wait(EC.presenceOf(element(by.className(cssClass))), timeOut);
    }

    /**
     * Wait for element is hidden
     */
    waitForElementHidden(cssClass: string, timeOut: number = 5000): webdriverPromise.Promise<any>  {
        const EC = protractor.ExpectedConditions;

        return browser.wait(EC.stalenessOf(element(by.className(cssClass))), timeOut);
    }

    /**
     * Press enter key
     */
    pressEnterKey(): webdriverPromise.Promise<any> {
        return browser.actions().sendKeys(protractor.Key.ENTER).perform();
    }

    /**
     * Wait for element attribute is hidden
     */
    waitForElementAttributeHidden(cssClass: string, attribute: string, timeOut: number = 5000): webdriverPromise.Promise<any>  {
        const EC = protractor.ExpectedConditions;

        return browser.wait(EC.stalenessOf(element(by.css('.' + cssClass + '[' + attribute + ']'))), timeOut);
    }

    /**
     * Wait for element attribute
     */
    waitForElementAttribute(cssClass: string, attribute: string, attributeValue: string = '', timeOut: number = 5000): webdriverPromise.Promise<any>  {
        const EC = protractor.ExpectedConditions;

        if (!attributeValue) {
            return browser.wait(EC.presenceOf(element(by.css('.' + cssClass + '[' + attribute + ']'))), timeOut);
        }

        return browser.wait(EC.presenceOf(element(by.css('.' + cssClass + '[' + attribute + '='+ attributeValue + ']'))), timeOut);
    }

    /**
     * Scroll area
     */
    async scrollArea(cssClass: string, position: 'top' | 'bottom', timeOut: number = 1000):  Promise<any> {
        const scrollPosition = position == 'top' ? 'start' : 'end';

        await browser.executeScript('arguments[0].scrollIntoView({behavior: "smooth", block: arguments[1]});', element(by.className(cssClass)), scrollPosition);

        return browser.sleep(timeOut);
    }

    /**
     * Move element
     */
    moveElement(cssClass: string, position: {x: number, y: number},  index: number = 0): webdriverPromise.Promise<any> {
        const action = browser.actions()
            .mouseDown(element.all(by.className(cssClass)).get(index));

        // hot fix described here - https://forum.ionicframework.com/t/move-ion-item-sliding-by-protractor/106918
        for (let i = 0; i < 100; i++) {
            action.mouseMove({
                x: position.x,
                y: position.y
            });
        }

        return action.mouseUp().perform();
    }

    /**
     * Set value to local store
     */
    setValueToLocalStore(name: string, value: any): void {
        browser.executeScript("window.localStorage.setItem('" + name + "', '" + JSON.stringify(value) + "')");
    }
}
