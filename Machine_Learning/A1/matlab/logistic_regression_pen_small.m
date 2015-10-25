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
hyperparameters.weight_regularization = 0.01;
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
diff = checkgrad('logistic_pen', ...
    randn((ndimensions + 1), 1), ...   % weights
    0.001,...                          % perturbation
    randn(nexamples, ndimensions), ... % data
    rand(nexamples, 1), ...            % targets
    hyperparameters)                   % other hyperparameters

N = size(train_inputs_small, 1);

avg_entropy_train = zeros(4, 1);
avg_entropy_valid = zeros(4, 1);
avg_frac_train = zeros(4, 1);
avg_frac_valid = zeros(4, 1);
counter = 0;

%% Begin learning with gradient descent.
for lamda = [0.001 0.01 0.1 1.0]
    counter = counter + 1;
    hyperparameters.weight_regularization = lamda;
    
    entropy_train = zeros(10, 1);
    entropy_valid = zeros(10, 1);
    frac_train = zeros(10, 1);
    frac_valid = zeros(10, 1);
    fprintf(1, 'When LAMDA = %.4f:\n', lamda);
    
    for i = 1:10
        for t = 1:hyperparameters.num_iterations
            
            %% TODO: You will need to modify this loop to create plots etc.
            
            % Find the negative log likelihood and derivative w.r.t. weights.
            [f, df, predictions] = logistic_pen(weights, ...
                train_inputs_small, ...
                train_targets_small, ...
                hyperparameters);
            
            [cross_entropy_train, frac_correct_train] = evaluate(train_targets_small, predictions);
            
            % Find the fraction of correctly classified validation examples.
            [temp, temp2, frac_correct_valid] = logistic_pen(weights, ...
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
            
        end
        
        %% Collect final cross entropy and classification error
        entropy_train(i) = cross_entropy_train;
        entropy_valid(i) = cross_entropy_valid;
        frac_train(i) = frac_correct_train;
        frac_valid(i) = frac_correct_valid;
        
        %% Print some stats.
        fprintf(1, 'RUN:%4i   TRAIN CE %.6f TRAIN FRAC:%2.2f VALIC_CE %.6f VALID FRAC:%2.2f\n',...
            i, cross_entropy_train, frac_correct_train*100, cross_entropy_valid, frac_correct_valid*100);
        
    end
    
    %% Average the evaluation metrics over different re-runs
    avg_entropy_train(counter) = sum(entropy_train)/10;
    avg_entropy_valid(counter) = sum(entropy_valid)/10;
    avg_frac_train(counter) = sum(frac_train)/10;
    avg_frac_valid(counter) = sum(frac_valid)/10;
    
    %% Print some stats.
    fprintf(1, '* LAMDA:%.4f   AVG TRAIN CE %.6f AVG TRAIN FRAC:%2.2f AVG_VALIC_CE %.6f AVG_FRAC:%2.2f\n\n',...
        lamda, avg_entropy_train(counter), avg_frac_train(counter)*100, avg_entropy_valid(counter), avg_frac_valid(counter)*100);
    
    %% Keep weights of best lamda
    if lamda == 0.01
        best_weights = weights;
    end
end

%% Compute test error
predictions_test = logistic_predict(best_weights, test_inputs);
[cross_entropy_test, frac_correct_test] = evaluate(test_targets, predictions_test);
fprintf('Test Error, when LAMDA = 0.01:\n');
fprintf(1, 'TEST_CE %.6f TEST FRAC:%2.2f\n',...
    cross_entropy_test, frac_correct_test*100);

%% Plot

x = [0.001 0.01 0.1 1.0];
plot(x, avg_entropy_train, '--*', x, avg_entropy_valid, '--*')
title('Avg Cross Entropy against Lamda on Small Training Set')
legend('Training', 'Validation')
xlabel('Lamda')
ylabel('Avg Cross Entropy')

figure
plot(x, avg_frac_train, '--*', x, avg_frac_valid, '--*')
axis([0 1 0.5 1])
title('Avg Classification Error against Lamda on Small Training Set')
legend('Training', 'Validation')
xlabel('Lamda')
ylabel('Avg Classification Error')
