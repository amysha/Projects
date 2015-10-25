%% Clear workspace.
clear all;
close all;

%% Load data.
load mnist_train_small;
load mnist_valid;
load mnist_test;

%% TODO: Initialize hyperparameters.
% Learning rate
hyperparameters.learning_rate = 1;
% Weight regularization parameter
hyperparameters.weight_regularization = 0;
% Number of iterations
hyperparameters.num_iterations = 300;
% Logistics regression weights
% TODO: Set random weights.
weights = randn(size(valid_inputs, 2)+1, 1);


%% Verify that your logistic function produces the right gradient, diff should be very close to 0
% this creates small random data with 20 examples and 10 dimensions and checks the gradient on
% that data.
nexamples = 20;
ndimensions = 10;
diff = checkgrad('logistic', ...
    randn((ndimensions + 1), 1), ...   % weights
    0.001,...                          % perturbation
    randn(nexamples, ndimensions), ... % data
    rand(nexamples, 1), ...            % targets
    hyperparameters)                   % other hyperparameters

N = size(train_inputs_small, 1);
cross_entropy_training = zeros(hyperparameters.num_iterations, 1);
cross_entropy_validation = zeros(hyperparameters.num_iterations, 1);

%% Begin learning with gradient descent.
for t = 1:hyperparameters.num_iterations
    
    %% TODO: You will need to modify this loop to create plots etc.
    
    % Find the negative log likelihood and derivative w.r.t. weights.
    [f, df, predictions] = logistic(weights, ...
        train_inputs_small, ...
        train_targets_small, ...
        hyperparameters);
    
    [cross_entropy_train, frac_correct_train] = evaluate(train_targets_small, predictions);
    
    % Find the fraction of correctly classified validation examples.
    [temp, temp2, frac_correct_valid] = logistic(weights, ...
        valid_inputs, ...
        valid_targets, ...
        hyperparameters);
    
    if isnan(f) || isinf(f)
        error('nan/inf error');
    end
    
    %% Update parameters.
    weights = weights - hyperparameters.learning_rate .* df / N;
    
    predictions_valid = logistic_predict(weights, valid_inputs);
    [cross_entropy_valid, frac_correct_valid] = evaluate(valid_targets, predictions_valid);
    
    %% Collect cross entropy data
    cross_entropy_training(t) = cross_entropy_train;
    cross_entropy_validation(t) = cross_entropy_valid;
    
    %% Print some stats.
    fprintf(1, 'ITERATION:%4i   NLOGL:%4.2f TRAIN CE %.6f TRAIN FRAC:%2.2f VALIC_CE %.6f VALID FRAC:%2.2f\n',...
        t, f/N, cross_entropy_train, frac_correct_train*100, cross_entropy_valid, frac_correct_valid*100);
    
end

%% Compute test error
predictions_test = logistic_predict(weights, test_inputs);
[cross_entropy_test, frac_correct_test] = evaluate(test_targets, predictions_test);
fprintf('Test Error:\n');
fprintf(1, 'TRAIN CE %.6f TRAIN FRAC:%2.2f TEST_CE %.6f TEST FRAC:%2.2f\n',...
    cross_entropy_train, frac_correct_train*100, cross_entropy_test, frac_correct_test*100);

%% Plot
x = (1:hyperparameters.num_iterations);
plot(x, cross_entropy_training, x, cross_entropy_validation)
title('Cross Entropy Changes on Small Training Set')
legend('Training', 'Validation')
xlabel('number of iterations')
ylabel('Cross Entropy')

