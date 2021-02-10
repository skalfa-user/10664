export interface IQuestionStructureValidatorsResponse {
    name: string,
    params: {}
}

export interface IQuestionStructureResponse {
    type: string,
    key?: string,
    label?: string,
    placeholder?: string,
    values?: any,
    value?: any,
    validators?: Array<IQuestionStructureValidatorsResponse>,
    params?: {}
}

export interface IQuestionListResponse {
    order?: number,
    sectionId?: number,
    section: string,
    items: Array<IQuestionStructureResponse>
}

export interface IQuestionResponse {
    id: string | number,
    name: string,
    value: any,
    type: string,
    params: {
        token?: string
    }
}
