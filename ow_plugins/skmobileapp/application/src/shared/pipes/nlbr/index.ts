import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'nlbr'
})

export class NlbrPipe implements PipeTransform {
    /**
     * Transform
     */
    transform(value: string): string {
        return value.replace(/(?:\r\n|\r|\n)/g, ' <br /> ');
    }
}
