import React from 'react';
import {Redirect} from '@docusaurus/router';

/**
 * Getting started was split into Introduction, Installation & configuration
 * and Your first payment; keep its old URL working until cutover.
 */
export default function GettingStarted() {
  return <Redirect to="/introduction" />;
}
