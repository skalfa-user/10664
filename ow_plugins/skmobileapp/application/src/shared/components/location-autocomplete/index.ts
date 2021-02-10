import { Component, OnInit, ChangeDetectionStrategy, ChangeDetectorRef } from '@angular/core';
import { ViewController, NavParams } from 'ionic-angular';

// services
import { LocationService } from 'services/location';

@Component({
    selector: 'location-autocomplete',
    templateUrl: 'index.html',
    changeDetection: ChangeDetectionStrategy.OnPush,
    providers: [
        LocationService
    ]
})

export class LocationAutocompleteComponent implements OnInit {
    isQuestionRequired: boolean;
    autocompleteItems: Array<string> = [];
    autocompleteLoading = false;
    debounceTime: number = 1500;
    searchQuery: string;   

    /**
     * Constructor
     */
    constructor(
        private ref: ChangeDetectorRef,
        private location: LocationService,
        private view: ViewController,
        private navParams: NavParams)
    {
        this.searchQuery = this.navParams.get('q');
        this.isQuestionRequired = this.navParams.get('isQuestionRequired');
    }

    /**
     * Component init
     */
    ngOnInit(): void {
        this.updateSearch();
    }

    /**
     * Keep empty
     */
    keepEmpty() {
        this.view.dismiss('');
    }

    /**
     * Cancel
     */
    cancel() {
        this.view.dismiss(null);
    }

    /**
     * Choose item
     */
    chooseItem(location: string) {
        this.view.dismiss(location);
    }

    /**
     * Update search
     */
    updateSearch() {
        if (!this.searchQuery) {
            this.autocompleteItems = [];

            return;
        }

        this.autocompleteLoading = true;
        this.location.loadAutocomplete(this.searchQuery).subscribe(response => {
            this.autocompleteLoading = false;
            this.autocompleteItems = response;

            this.ref.markForCheck();
        }, () => this.view.dismiss(null));
    }
}
