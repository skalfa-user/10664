export interface IViewQuestionResponse {
    [index: number]: {
        order: number; 
        section: string; 
        items: Array<{
            name: string, 
            label: string, 
            value: string
        }>
    };
}