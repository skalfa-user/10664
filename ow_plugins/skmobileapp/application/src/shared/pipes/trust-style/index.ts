import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeStyle } from '@angular/platform-browser';

@Pipe({
    name: 'trustStyle'
})

export class TrustStylePipe implements PipeTransform {
    /**
     * Constructor
     */
    constructor(private sanitizer: DomSanitizer) {}

    /**
     * Transform
     */
    transform(value: string): SafeStyle {
        return this.sanitizer.bypassSecurityTrustStyle(value);
    }
}
