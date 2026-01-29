import '../../support/pkp-mock.js';
import CodecheckCertificateIdentifier from '../../../resources/js/Components/CodecheckCertificateIdentifier.vue';

describe('CodecheckCertificateIdentifier Component', () => {
  beforeEach(() => {
    cy.intercept('POST', '**/codecheck_api/getVenueData', {
        statusCode: 200,
        body: {
        success: true,
        venueTypes: ['Journal', 'Conference', 'Preprint'],
        venueNames: ['Nature', 'Science', 'PLOS ONE']
        }
    }).as('getVenueData');
  });

  it('renders the identifier input field', () => {
    cy.mount(CodecheckCertificateIdentifier, {
      props: {
        name: 'certificate',
        value: ''
      }
    });
    
    cy.get('.certificate-identifier-input').should('exist');
  });

  it('loads venue types and names on mount', () => {
    cy.mount(CodecheckCertificateIdentifier, {
      props: {
        name: 'certificate',
        value: ''
      }
    });
    
    cy.wait('@getVenueData');
    
    cy.get('.certificate-identifier-venue-types option').should('have.length.gt', 1);
    cy.get('.certificate-identifier-venue-names option').should('have.length.gt', 1);
  });

  it('disables selects when identifier is reserved', () => {
    cy.mount(CodecheckCertificateIdentifier, {
      props: {
        name: 'certificate',
        value: '2025-001'
      }
    });
    
    cy.get('.certificate-identifier-venue-types').should('be.disabled');
    cy.get('.certificate-identifier-venue-names').should('be.disabled');
    cy.get('.certificate-identifier-button').first().should('be.disabled');
  });

  it('can reserve an identifier', () => {
    cy.intercept('POST', '**/codecheck_api/reserveIdentifier', {
      statusCode: 200,
      body: {
        success: true,
        identifier: '2025-042',
        issueUrl: 'https://github.com/example/issues/42'
      }
    }).as('reserveIdentifier');

    cy.mount(CodecheckCertificateIdentifier, {
      props: {
        name: 'certificate',
        value: ''
      }
    });
    
    cy.wait('@getVenueData');
    
    cy.get('.certificate-identifier-venue-types').select('Journal');
    cy.get('.certificate-identifier-venue-names').select('Nature');
    
    cy.get('.certificate-identifier-button.bg-blue').click();
    
    cy.wait('@reserveIdentifier');
    
    cy.get('.certificate-identifier-input').should('have.value', '2025-042');
    cy.contains('View GitHub Issue').should('exist');
  });

  it('can remove an identifier', () => {
    cy.mount(CodecheckCertificateIdentifier, {
      props: {
        name: 'certificate',
        value: '2025-001'
      }
    });
    
    cy.get('.certificate-identifier-button.bg-red').click();
    
    cy.get('.certificate-identifier-input').should('have.value', '');
  });

  it('shows alert when trying to reserve without selections', () => {
    cy.mount(CodecheckCertificateIdentifier, {
      props: {
        name: 'certificate',
        value: ''
      }
    });
    
    cy.wait('@getVenueData');
    
    cy.on('window:alert', (text) => {
      expect(text).to.contains('Please select');
    });
    
    cy.get('.certificate-identifier-button.bg-blue').click();
  });
});