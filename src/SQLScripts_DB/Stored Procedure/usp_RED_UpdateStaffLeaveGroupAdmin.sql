USE [BBCSchedules]
GO
/****** Object:  StoredProcedure [dbo].[usp_RED_UpdateStaffLeaveGroupAdmin]    Script Date: 24/09/2025 17:40:50 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER    PROCEDURE [dbo].[usp_RED_UpdateStaffLeaveGroupAdmin] 
    @intGroup			    INTEGER,
	@intAction			    INTEGER,
	@strStaffLogin			VARCHAR(50),
	@Fullname			    NVARCHAR(100),
	@currentuserid INT,
	@modulename varchar(200)

AS
BEGIN

	SET NOCOUNT ON;
	DECLARE @return_value int

	BEGIN TRY
		
		declare @LeaveRequestGroupDesc NVARCHAR(100),
				@sql NVARCHAR(max),
				@newid INT,@historytype VARCHAR(max),
				@ScheduledPersonID INT;

		SELECT @ScheduledPersonID = UD_UserID
		 FROM UserDetails UD
		WHERE UD_NetLogin = @strStaffLogin

		SELECT  @newid = ID 
		  from Staff_Web_Config_LeaveGroups_Link 
		 WHERE ScheduledPersonID = @ScheduledPersonID
		   AND LeaveGroupID = @intGroup


		-- Get the group Name
		SELECT  @LeaveRequestGroupDesc =  Description
        FROM    LeaveRequestGroups
        WHERE   (ID = @intGroup)

		DECLARE @History nvarchar(max) = (
          CASE @intAction
		    WHEN  1 THEN 'Record updated by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) + ' at ' 
			              + CONVERT(VARCHAR(5),getdate(),108)  
						  + '. The user was made an Authorisor for Leave/Request Group ''' 
						  +  @LeaveRequestGroupDesc + '''.<hr>'
		    WHEN  2 THEN 'Record updated by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) 
			              + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
			              + '. The user was made an Administrator for Leave/Request Group ''' 
						  +  @LeaveRequestGroupDesc + '''.<hr>'
		    WHEN  3 THEN 'Record updated by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) 
			              + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
			              + '. The user was made an Leave Manager for Leave/Request Group ''' 
						  +  @LeaveRequestGroupDesc + '''.<hr>'          		  
		  ELSE
		                  'Record updated by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) 
						  + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  
						  + '. The user was removed as an Administrator for Leave/Request Group ''' 
						  +  @LeaveRequestGroupDesc + '''.<hr>'
		end);
		 

        UPDATE Staff_Web_Config_LeaveGroups_Link
           SET Admin = @intAction
         WHERE ScheduledPersonID = @ScheduledPersonID
		   AND LeaveGroupID = @intGroup

		
		Select @historytype = id 
		 from HistoryTypes 
		 where HistoryType = @modulename

		INSERT INTO History([HistoryType],[UserID],[History],[datetime],[AttributeID])
		VALUES (@historytype ,@currentuserid,@History,GETDATE(),@newid)

		SELECT  'ReturnValue' = 0;
		
		RETURN 0;
			    
	END TRY
	BEGIN CATCH

		 INSERT INTO ErrorLog
		        (ErrorNumber,
				 ErrorState,
				 ErrorSeverity,
				 ErrorProcedure,
				 ErrorLine,
				 ErrorMessage,
				 ErrorDateTime,
				 UserName
				)
         SELECT ERROR_NUMBER() AS ErrorNumber,
                ERROR_STATE() AS ErrorState,
				ERROR_SEVERITY() AS ErrorSeverity,
				ERROR_PROCEDURE() AS ErrorProcedure,
				ERROR_LINE() AS ErrorLine,
				ERROR_MESSAGE() AS ErrorMessage,
				getutcdate(),
				@currentuserid	
		
	    SELECT  
		 ERROR_NUMBER() AS ErrorNumber  
        ,ERROR_SEVERITY() AS ErrorSeverity  
        ,ERROR_STATE() AS ErrorState  
        ,ERROR_PROCEDURE() AS ErrorProcedure  
        ,ERROR_LINE() AS ErrorLine  
        ,ERROR_MESSAGE() AS ErrorMessage; 

	   REVERT;

	END CATCH

END