import { Component, DebugElement } from '@angular/core';
import { ComponentFixture, TestBed } from '@angular/core/testing';
import { ChangeFocusByEnterDirective } from './';
import { By } from '@angular/platform-browser';
import { TextInput } from 'ionic-angular';

// test component
@Component({
    template: `<input changeFocusByEnter type="text" />` 
})
class TestChangeFocusByEnterComponent {
}

describe('Change focus by enter directive', () => {
    let fixture: ComponentFixture<TestChangeFocusByEnterComponent>;
    let inputEl: DebugElement;
    let textInputFake: TextInput;

    beforeEach(() => {
        TestBed.configureTestingModule({
            declarations: [
                TestChangeFocusByEnterComponent, 
                ChangeFocusByEnterDirective
            ],
            providers: [
                {
                    provide: TextInput,
                    useFactory: () =>  jasmine.createSpyObj('TextInput', ['focusNext']),
                    deps: []
                }
            ]
        });

        textInputFake = TestBed.get(TextInput);
        fixture = TestBed.createComponent(TestChangeFocusByEnterComponent); 
        inputEl = fixture.debugElement.query(By.css('input'));
    });

    it('keydown enter should pass the focus to another input ', () => {
        inputEl.triggerEventHandler('keydown', {
            keyCode: 13
        });
 
        fixture.detectChanges();

        expect(textInputFake.focusNext).toHaveBeenCalled();
    });
});
