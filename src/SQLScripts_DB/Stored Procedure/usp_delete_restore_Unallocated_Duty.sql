USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_delete_restore_Unallocated_Duty]    Script Date: 10/07/2025 13:02:20 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
ALTER     PROCEDURE [dbo].[usp_delete_restore_Unallocated_Duty]
	-- Add the parameters for the stored procedure here
	@DutyID				INT,
	@WeekNumber         INT,
	@TeamID             INT,
	@CurrentuserID		INT, 
	@Currentuser		VARCHAR(50) ,
	@isActive           INT = 1,
	@IsShiftleader      INT = NULL

AS
BEGIN

	SET NOCOUNT ON;
	
	DECLARE @JobCount      INT,
			@AllocationsID INT,
			@ReturnValue	INT = 0,
			@PublishStatus	INT = 0,
			@pNetLogin		VARCHAR(20);

	 SELECT @pNetLogin = UD_NetLogin
	   FROM UserDetails 
	  WHERE UD_UserID = @CurrentuserID
	
	 UPDATE AllocationsJobs 
	    SET AJ_JobStatus = CASE WHEN @isActive = 1
								THEN 0
								WHEN @isActive = 0
								THEN 9 END
	  WHERE AJ_AllocationsDutyID = @DutyID 
	  
	 SET @JobCount = @@ROWCOUNT 
	  
	 IF ( @JobCount > 0 )
	  BEGIN
	   
	    	INSERT INTO History 
			          ( HistoryType,
					    UserID,
						History,
						datetime,
						AttributeID ) 
				 SELECT HT.ID,
				        @CurrentuserID,
						'Job '+AJ_JobName
						+ CASE WHEN @isActive = 0 THEN 'Deleted On ' 
						       ELSE 'Restored On ' END
						+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						+' by '+@Currentuser+'.',
						getdate(),
						AJ_AllocateJobID
				   FROM AllocationsJobs AJ
				   INNER JOIN HistoryTypes HT ON 1=1
				   WHERE AJ_AllocationsDutyID = @DutyID
				     AND HT.HistoryType = 'AllocationJobs'
	  
	  END


			 UPDATE AllocationsDuties 
			    SET AD_DutyStatus = CASE WHEN @isActive = 1
										 THEN 0
										 WHEN @isActive = 0
										 THEN 9 END,				     
				    AD_IsEditedDutyAttention = case when @IsShiftleader = 1 
													then 1 else AD_IsEditedDutyAttention 
													end,
					AD_UpdatedBy = @CurrentuserID,
					AD_UpdatedDate = GETUTCDATE()
			  WHERE AD_AllocationsDutyID = @DutyID 

			  SELECT @AllocationsID = AL_AllocationsID
			    FROM Allocations AL
			   INNER JOIN AllocationsDuties AD on AL_AllocationsID = AD_AllocationsID 
			   WHERE AD_AllocationsDutyID = @DutyID
			
	    	INSERT INTO History 
			          ( HistoryType,
					    UserID,
						History,
						datetime,
						AttributeID,
						HistorySubType ) 
				 SELECT HT.ID,
				        @CurrentuserID,
						'Duty '+AD_DutyName
						+case when ISNULL(@JobCount,0) > 0 then
						' has '+cast(@JobCount as Nvarchar) +' Jobs' 
						else '' end
						+' - '
						+ CASE WHEN @isActive = 0 THEN 'Deleted On ' 
						       ELSE 'Restored On ' END
						+ FORMAT(Getdate(),'dd/MM/yyyy HH:mm')
						+' by '+@Currentuser+'.',
						getdate(),
						AD_AllocationsDutyID,
						'DH'
				   FROM AllocationsDuties AL
				   INNER JOIN HistoryTypes HT ON 1=1
				   WHERE AD_AllocationsDutyID = @DutyID
				     AND HT.HistoryType = 'AllocationDuty'	
					 


				SET @PublishStatus = CASE WHEN @IsShiftleader = 1 THEN 1 ELSE 0 END
				
				EXEC @ReturnValue = usp_CreateAllocationsUpdate  @AllocationsID,
																 @DutyID,
																 0,
																 @PublishStatus,
																 @pNetLogin
																 
				IF ( @ReturnValue > 0 )
				  BEGIN
					THROW 51000, 'Error while creating Duty update status', 1;
				  END 				
					 
			IF ( @IsShiftleader = 1 )
			 BEGIN			   
               EXEC usp_mod_PublishIndividulAllocations @AllocationsID,@DutyID, 0				   
			 END

END