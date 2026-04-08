USE [Allocate7]
GO
/****** Object:  StoredProcedure [dbo].[usp_RED_InsAddStaffToLeaveGroup]    Script Date: 31/10/2022 11:40:56 ******/
SET ANSI_NULLS ON
GO
SET QUOTED_IDENTIFIER ON
GO
CREATE OR ALTER      PROCEDURE [dbo].[usp_RED_InsAddStaffToLeaveGroup] 
	-- Add the parameters for the stored procedure heres
	@intLeaveGroup			    INTEGER,
	@strStaffLogin			VARCHAR(50),
	@staffsheduledPersonId	int,
	@Fullname			VARCHAR(50),
	@currentuserid INT,
	@modulename varchar(200)
	
AS
BEGIN
	-- SET NOCOUNT ON added to prevent extra result sets from
	-- interfering with SELECT statements.
	SET NOCOUNT ON;
		DECLARE @return_value int
	BEGIN TRY
		
		declare @LeaveGroupName NVARCHAR(100);
		declare @History NVARCHAR(max),@historytype VARCHAR(max);
		declare @isactive int,@staffgroupid int,@isactiveval int
		 

		-- Get the Leave Group Name
		SELECT   @LeaveGroupName = Description
		FROM     LeaveRequestGroups
		WHERE    (ID = @intLeaveGroup)


		
				IF NOT EXISTS (select top(1) * from Staff_Web_Config_LeaveGroups_Link (NOLOCK) WHERE LeaveGroupID =@intLeaveGroup and Login = @strStaffLogin )
						BEGIN
								INSERT        INTO    Staff_Web_Config_LeaveGroups_Link(Login, LeaveGroupID,ScheduledPersonID)
								VALUES        (@strStaffLogin,@intLeaveGroup,@staffsheduledPersonId)
						
								SET @staffgroupid = Scope_Identity();
							   set @History = 'Record inserted by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)  + '. Access to Leave Group ' + @LeaveGroupName + ' was granted.<hr>'
						END
				ELSE
						BEGIN
							select @isactive = isActive,@staffgroupid = ID from Staff_Web_Config_LeaveGroups_Link (NOLOCK) WHERE LeaveGroupID =@intLeaveGroup and Login = @strStaffLogin 
								if @isactive= 0
								BEGIN
									set @isactiveval =1
									 set @History = 'Record updated by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)   + '. Access to Leave Group ' + @LeaveGroupName + ' was granted.<hr>'
								END
								ELSE
								BEGIN 
									set @isactiveval =0
									 set @History = 'Record updated by ' +  @Fullname + ' on ' +  CONVERT(VARCHAR, GETDATE(), 103) + ' at ' + CONVERT(VARCHAR(5),getdate(),108)   + '. Access to Leave Group ' + @LeaveGroupName + ' was removed.<hr>'
								END
								Update Staff_Web_Config_LeaveGroups_Link set isActive=@isactiveval where ID =@staffgroupid

						END
		

		-- add the History
						Select @historytype = id from HistoryTypes where HistoryType = @modulename
						INSERT INTO [dbo].[History]([HistoryType],[UserID],[History],[datetime],[AttributeID])
						VALUES (@historytype ,@currentuserid,@History,GETDATE(),@staffgroupid)
		SELECT  'ReturnValue' = 1,'StrStatus'= 'User Add sucessfully';
		RETURN 1;
			    
	END TRY
	BEGIN CATCH
		REVERT;
	    SELECT  'ReturnValue' = 0,'StrStatus'= 'Error in executing statement 3';
		RETURN 0;
	END CATCH

END

