import { Pipe, PipeTransform } from '@angular/core';

@Pipe({
    name: 'urlify'
})

export class UrlifyPipe implements PipeTransform {
    /**
     * Transform
     */
    transform(value: string): string {
        return value.replace(/(https?:\/\/|ftp:\/\/|www\.)((?![.,?!;:()]*(\s|$))[^\s]){2,}/gmi, (url) => {
            // convert to lower case
            url = url.toLowerCase();

            if (url.search(/^http[s]?\:\/\/|^ftp:\/\//) == -1) {
                return  '<a href="http://' + url + '">' + url + '</a>';
            }

            return '<a href="' + url + '">' + url + '</a>';
        });
    }
}
