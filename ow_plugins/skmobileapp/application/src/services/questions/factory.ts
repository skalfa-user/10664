import { ModalController } from 'ionic-angular';
import { QuestionManager } from './manager';

/**
 * Question manager factory
 */
export function questionManagerFactory (
    modalCtrl: ModalController
): QuestionManager {
    return new QuestionManager(modalCtrl);
}
